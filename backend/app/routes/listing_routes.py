from fastapi import APIRouter, Depends, HTTPException
from app.services.listing_services import create_listing, get_users_listings, get_listing, update_listing
from app.models.listing_models import IndividualListing, individual, IndividualListingUpdate
from motor.motor_asyncio import AsyncIOMotorDatabase
from app.db.mongodb import MongoConnection

router = APIRouter(prefix="/listings", tags=["Listings"])

@router.get("/get_listing/{id}")
async def get_listing_endpoint(id: str):
    db: AsyncIOMotorDatabase = await MongoConnection.get_db()
    collection = db.individual_listings
    listing = await get_listing(id, collection)
    if not listing:
        raise HTTPException(status_code=404, detail="Listing not found")
    return {"listing": listing}

@router.patch("/update_listing/{id}")
async def update_listing_endpoint(id: str, data: IndividualListingUpdate):
    db: AsyncIOMotorDatabase = await MongoConnection.get_db()
    collection = db.individual_listings
    success = await update_listing(id, data, collection)
    if not success:
        raise HTTPException(status_code=400, detail="Failed to update listing")
    return {"success": True, "message": "Listing updated successfully"}

@router.get("/get_user_listings/{uid}")
async def get_user_listings(uid: str):
    db: AsyncIOMotorDatabase = await MongoConnection.get_db()
    collection = db.individual_listings

    listings = await get_users_listings(uid, collection)
    return {"listings": listings}


@router.post("/create_user_listing")
async def create_user_listings(data: IndividualListing):
    return await create_listing(data)