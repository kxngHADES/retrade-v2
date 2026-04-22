from fastapi import APIRouter, Depends
from app.services.listing_services import create_listing, get_users_listings
from app.models.listing_models import IndividualListing, individual
from motor.motor_asyncio import AsyncIOMotorDatabase
from app.db.mongodb import MongoConnection

router = APIRouter(prefix="/listings", tags=["Listings"])


@router.get("/get_user_listings/{uid}")
async def get_user_listings(uid: str):
    db: AsyncIOMotorDatabase = await MongoConnection.get_db()
    collection = db.individual_listings

    listings = await get_users_listings(uid, collection)
    return {"listings": listings}


@router.post("/create_user_listing")
async def create_user_listings(data: IndividualListing):
    return await create_listing(data)