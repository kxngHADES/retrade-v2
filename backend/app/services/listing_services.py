from app.db.mongodb import individual_listings_collection, MongoConnection
from app.models.listing_models import individual, IndividualListing, IndividualListingUpdate
from app.db.neo4j import Neo4jConnection
from app.utils.vector_db import add_listing_to_vector_db
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
    except Exception as e:
        print(f"Error fetching listing {listing_id}: {e}")
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
    except Exception as e:
        print(f"Error updating listing {listing_id}: {e}")
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

"""
async def get_users_listings(uid: str, collection: AsyncIOMotorCollection) -> list[dict]:
    cache_key = f"user_listings:{uid}"
    
    # 🔍 TEMP: Bypass Redis to see raw DB data
    print(f"🔍 Querying MongoDB for uid='{uid}'")
    
    listings_cursor = collection.find({"uid": uid})
    listings = await listings_cursor.to_list(length=20)
    
    print(f"📦 MongoDB returned {len(listings)} documents")
    if listings:
        print(f"👀 First doc structure: {listings[0].keys()}")
        print(f"👀 Stored UID in DB: '{listings[0].get('uid')}'")
        
    return listings
"""

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
    except Exception as e:
        print(f"⚠️ Warning: Failed to embed listing {listing_id}: {e}")

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