from app.db.mongodb import individual_listings_collection
from app.models.listing_models import individual, IndividualListing


async def get_users_listings(data: individual):
    listings = await individual_listings_collection.find({"uid": data.uid}).to_list(length=20)

    return listings

async def create_listing(data: IndividualListing):
    result = await individual_listings_collection.insert_one(data.dict())

    return {
        "inserted_id": str[result.inserted_id]
    }

    # TODO add a table in MySQL to create a user to document refrence