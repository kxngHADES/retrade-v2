from app.db.mongodb import individual_listings_collection, MongoConnection
from app.models.listing_models import individual, IndividualListing, IndividualListingUpdate, ListingSearchParams
from app.db.neo4j import Neo4jConnection
from app.db.elasticsearch import ElasticsearchConnection
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

async def delete_listing(listing_id: str, collection: AsyncIOMotorCollection) -> bool:
    try:
        result = await collection.delete_one({"_id": ObjectId(listing_id)})
        return result.deleted_count > 0
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



async def search_listings_in_es(search_params: ListingSearchParams) -> dict:
    es_client = ElasticsearchConnection.get_client()
    
    must_clauses = [{"multi_match": {
        "query": search_params.query,
        "fields": ["name^3", "description", "tags"]
    }}]

    filter_clauses = []
    if search_params.category:
        filter_clauses.append({"term": {"category.keyword": search_params.category}})
    if search_params.condition:
        filter_clauses.append({"term": {"condition.keyword": search_params.condition}})
    if search_params.location:
        filter_clauses.append({"match": {"location": search_params.location}})
    
    if search_params.min_price is not None or search_params.max_price is not None:
        price_range = {}
        if search_params.min_price is not None:
            price_range["gte"] = search_params.min_price
        if search_params.max_price is not None:
            price_range["lte"] = search_params.max_price
        filter_clauses.append({"range": {"price": price_range}})

    es_query = {
        "bool": {
            "must": must_clauses,
            "filter": filter_clauses
        }
    }
    
    try:
        response = await es_client.search(
            index=settings.ELASTICSEARCH_INDEX,
            query=es_query,
            size=50
        )
        hits = [hit["_source"] for hit in response.get("hits", {}).get("hits", [])]
        for hit in hits:
            hit["id"] = hit.get("id") or hit.get("_id")
        results = {"total": response.get("hits", {}).get("total", {}).get("value", 0), "listings": hits, "source": "elasticsearch"}
        
        if results["total"] == 0:
            print("Elasticsearch returned 0 hits, falling back to MongoDB search")
            return await search_listings_in_mongo(search_params)
            
        return results
    except Exception as e:
        print(f"Elasticsearch query failed: {e}")
        return await search_listings_in_mongo(search_params)

async def search_listings_in_mongo(search_params: ListingSearchParams) -> dict:
    db = await MongoConnection.get_db()
    collection = db.individual_listings
    
    query_filter = {}
    
    # Simple regex search across multiple fields
    if search_params.query:
        regex_pattern = {"$regex": search_params.query, "$options": "i"}
        query_filter["$or"] = [
            {"name": regex_pattern},
            {"description": regex_pattern},
            {"tags": regex_pattern}
        ]
        
    if search_params.category:
        query_filter["category"] = search_params.category
    if search_params.condition:
        query_filter["condition"] = search_params.condition
    if search_params.location:
        query_filter["location"] = {"$regex": search_params.location, "$options": "i"}
        
    if search_params.min_price is not None or search_params.max_price is not None:
        price_filter = {}
        if search_params.min_price is not None:
            price_filter["$gte"] = search_params.min_price
        if search_params.max_price is not None:
            price_filter["$lte"] = search_params.max_price
        query_filter["price"] = price_filter
        
    try:
        listings = await collection.find(query_filter).sort("_id", -1).limit(50).to_list(length=50)
        
        for doc in listings:
            if "_id" in doc and hasattr(doc["_id"], "__str__"):
                doc["_id"] = str(doc["_id"])
                doc["id"] = doc["_id"]
                doc["thumbnail_url"] = doc.get("thumbnail_url", "")
                
        return {"total": len(listings), "listings": listings, "source": "mongodb"}
    except Exception as e:
        print(f"MongoDB search failed: {e}")
        return {"total": 0, "listings": []}

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

async def handle_payout_notifications(seller_email: str, amount: float) -> bool:
    try:
        from app.utils.email_helper import send_email
        await send_email(
            to_email=seller_email,
            template_name="payout",
            subject="ReTrade - Funds Released!",
            amount=f"{amount:.2f}"
        )
        return True
    except Exception as e:
        print(f"Failed to send payout notification: {e}")
        return False

#listings = [clean_mongo_doc(doc) for doc in raw_listings]