from app.db.mongodb import individual_listings_collection, MongoConnection
from app.models.listing_models import individual, IndividualListing
from app.db.neo4j import Neo4jConnection
from app.utils.vector_db import add_listing_to_vector_db


async def get_users_listings(data: individual):
    listings = await individual_listings_collection.find({"uid": data.uid}).to_list(length=20)

    return listings

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
            listing_id=listing_id,
            text_to_embed=text_to_embed,
            payload=payload
        )
    except Exception as e:
        print(f"⚠️ Warning: Failed to embed listing {listing_id}: {e}")

    return {
        "inserted_id": listing_id
    }