from app.db.mongodb import individual_listings_collection, MongoConnection
from app.models.listing_models import individual, IndividualListing, IndividualListingUpdate
from app.db.neo4j import Neo4jConnection
from app.utils.vector_db import add_listing_to_vector_db, search_similar_listings
import redis.asyncio as redis
from app.core.config import settings
import json
from motor.motor_asyncio import AsyncIOMotorCollection
from bson import ObjectId

redis_client: redis.Redis | None = None

async def get_listing(listing_id: str, collection: AsyncIOMotorCollection) -> dict | None:
    try:
        listing = await collection.find_one({"_id": ObjectId(listing_id)})
        if listing:
            listing = clean_mongo_doc(listing)
        return listing
    except Exception:
        return None

async def update_listing(listing_id: str, update_data: IndividualListingUpdate, collection: AsyncIOMotorCollection) -> bool:
    try:
        update_dict = {k: v for k, v in update_data.dict(exclude_unset=True).items() if v is not None}
        if not update_dict:
            return False
            
        result = await collection.update_one(
            {"_id": ObjectId(listing_id)},
            {"$set": update_dict}
        )
        return result.modified_count > 0
    except Exception:
        return False


async def get_users_listings(uid: str, collection: AsyncIOMotorCollection) -> list[dict]:
    global redis_client
    cache_key = f"user_listings:{uid}"

    if redis_client:
        cached = await redis_client.get(cache_key)
        if cached:
            return json.loads(cached)

    listings = await collection.find({"uid": uid}).to_list(length=20)

    for doc in listings:
        if "_id" in doc and hasattr(doc["_id"], "__str__"):
            doc["_id"] = str(doc["_id"])

    if redis_client and listings:
        await redis_client.setex(
            cache_key,
            settings.REDIS_CACHE_TTL,
            json.dumps(listings)
        )

    return listings


async def get_latest_listings(collection: AsyncIOMotorCollection) -> list[dict]:
    listings = await collection.find().sort("_id", -1).limit(50).to_list(length=50)
    for doc in listings:
        if "_id" in doc and hasattr(doc["_id"], "__str__"):
            doc["_id"] = str(doc["_id"])
    return listings


async def get_recommendations(uid: str, collection: AsyncIOMotorCollection, page_num: int = 1) -> list[dict]:
    global redis_client
    cache_key = f"recommendations:{uid}:{page_num}"
    
    if redis_client:
        cached = await redis_client.get(cache_key)
        if cached:
            return json.loads(cached)

    db = await MongoConnection.get_db()
    views_collection = db.user_views

    # Get user's last 5 viewed items
    recent_views = await views_collection.find({"uid": uid}).sort("viewed_at", -1).limit(5).to_list(length=5)
    
    if not recent_views:
        # Fallback to random/latest stuff excluding user's own listings if no views
        listings = await collection.find({"uid": {"$ne": uid}}).sort("_id", -1).skip((page_num - 1) * 50).limit(50).to_list(length=50)
    else:
        # Construct a query text from recent views to get similar vectors
        viewed_ids = [ObjectId(v['listing_id']) for v in recent_views if ObjectId.is_valid(v['listing_id'])]
        viewed_listings = await collection.find({"_id": {"$in": viewed_ids}}).to_list(length=5)
        
        query_parts = []
        for l in viewed_listings:
            query_parts.append(f"{l.get('name', '')} {l.get('category', '')}")
        
        query_text = " ".join(query_parts)
        
        if not query_text.strip():
            # Fallback
            listings = await collection.find({"uid": {"$ne": uid}}).sort("_id", -1).skip((page_num - 1) * 50).limit(50).to_list(length=50)
        else:
            try:
                # Ask Qdrant for similar items based on aggregated text
                # We ask for a higher limit in case we need to filter out user's own or already seen listings,
                # then paginate locally to approximate basic recommendation paging.
                # In real prod we'd offset directly with qdrant paginations.
                limit_needed = page_num * 50
                similar_points = await search_similar_listings(query_text, limit=limit_needed + 50)
                
                # Fetch matching documents from Mongo using Qdrant refs
                if similar_points:
                    mongo_ids = [ObjectId(p['mongo_id']) for p in similar_points if 'mongo_id' in p and ObjectId.is_valid(p['mongo_id'])]
                    
                    cursor = collection.find({
                        "_id": {"$in": mongo_ids},
                        "uid": {"$ne": uid}  # exclude their own listings
                    })
                    all_similar_listings = await cursor.to_list(length=limit_needed + 50)
                    
                    # Re-sort to maintain Qdrant's ranking (which is roughly sorted by score)
                    id_map = {str(doc['_id']): doc for doc in all_similar_listings}
                    ordered_listings = []
                    
                    for p in similar_points:
                        mid = p.get('mongo_id')
                        if mid in id_map:
                            ordered_listings.append(id_map[mid])
                            del id_map[mid] # prevent duplicates if qdrant returns same ref
                    
                    # Manual pagination bounds stringency
                    start_idx = (page_num - 1) * 50
                    end_idx = start_idx + 50
                    listings = ordered_listings[start_idx:end_idx]
                else:
                    listings = []
                
                if not listings:
                    listings = await collection.find({"uid": {"$ne": uid}}).sort("_id", -1).skip((page_num - 1) * 50).limit(50).to_list(length=50)
            except Exception as e:
                # Fallback on vector DB error
                print(f"Vector DB recommendation error: {e}")
                listings = await collection.find({"uid": {"$ne": uid}}).sort("_id", -1).skip((page_num - 1) * 50).limit(50).to_list(length=50)

    for doc in listings:
        if "_id" in doc and hasattr(doc["_id"], "__str__"):
            doc["_id"] = str(doc["_id"])
            doc["thumbnail_url"] = doc.get("thumbnail_url", "")
            
    if redis_client and listings:
        await redis_client.setex(
            cache_key,
            settings.REDIS_CACHE_TTL,
            json.dumps(listings)
        )
    return listings


import datetime
async def record_user_view(uid: str, listing_id: str, views_collection: AsyncIOMotorCollection):
    await views_collection.insert_one({
        "uid": uid,
        "listing_id": listing_id,
        "viewed_at": datetime.datetime.utcnow()
    })


async def link_user_to_listing(uid: str, listing_id: str):
    driver = await Neo4jConnection.get_driver()

    async with driver.session() as session:
        await session.execute_write(
            lambda tx: tx.run(
                """
                MERGE (u:User {uid: $uid})
                MERGE (l:Listing {id: $listing_id})
                MERGE (u)-[:LISTED]->(l)
                """,
                uid=uid,
                listing_id=listing_id
            )
        )

async def create_listing(data: IndividualListing):
    db = await MongoConnection.get_db()
    
    result = await db.individual_listings.insert_one(data.dict())

    listing_id = str(result.inserted_id)

    await link_user_to_listing(data.uid, listing_id)

    text_to_embed = f"{data.name} {data.description} {' '.join(data.tags)}"

    payload = {
        "name": data.name,
        "category": data.category,
        "price": data.price,
        "condition": data.condition,
        "location": data.location,
        "delivery_method": data.delivery_method,
        "uid": data.uid,
    }

    try:
        await add_listing_to_vector_db(
            mongo_listing_id=listing_id,
            text_to_embed=text_to_embed,
            payload=payload
        )
    except Exception:
        pass

    return {
        "inserted_id": listing_id
    }



def clean_mongo_doc(doc: dict) -> dict:
    if isinstance(doc, dict):
        return {
            key: clean_mongo_doc(value) 
            for key, value in doc.items()
        }
    elif isinstance(doc, list):
        return [clean_mongo_doc(item) for item in doc]
    elif hasattr(doc, "__str__") and type(doc).__name__ == "ObjectId":
        return str(doc)
    return doc


#listings = [clean_mongo_doc(doc) for doc in raw_listings]