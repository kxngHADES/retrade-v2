from fastapi import APIRouter, Depends, HTTPException, Query
from app.services.listing_services import create_listing, get_users_listings, get_listing, update_listing, get_latest_listings, get_recommendations, record_user_view, search_listings_in_es
from app.models.listing_models import IndividualListing, individual, IndividualListingUpdate, ListingSearchParams
from pydantic import BaseModel, EmailStr
from motor.motor_asyncio import AsyncIOMotorDatabase
from app.db.mongodb import MongoConnection
from app.services.order_services import handle_escrow_notifications

router = APIRouter(prefix="/listings", tags=["Listings"])

class EscrowNotificationRequest(BaseModel):
    buyer_email: EmailStr
    seller_email: EmailStr
    reference: str
    pin: str

class ViewRequest(BaseModel):
    uid: str
    listing_id: str

@router.get("/latest")
async def latest_listings():
    db: AsyncIOMotorDatabase = await MongoConnection.get_db()
    listings = await get_latest_listings(db.individual_listings)
    return {"listings": listings}


@router.get("/recommendations/{uid}")
async def fetch_recommendations(uid: str, page: int = Query(1, alias="page")):
    db: AsyncIOMotorDatabase = await MongoConnection.get_db()
    # returns 50 recs per page
    listings = await get_recommendations(uid, db.individual_listings, page)
    return {"listings": listings}


@router.post("/record_view")
async def record_view(data: ViewRequest):
    db: AsyncIOMotorDatabase = await MongoConnection.get_db()
    collection = db.user_views
    await record_user_view(data.uid, data.listing_id, collection)
    return {"success": True}

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

@router.post("/search")
async def search_listings(search_params: ListingSearchParams):
    results = await search_listings_in_es(search_params)
    return results

# Orders

@router.post("/escrow_notifications")
async def trigger_escrow_notifications(data: EscrowNotificationRequest):
    success = await handle_escrow_notifications(
        buyer_email=data.buyer_email,
        seller_email=data.seller_email,
        reference=data.reference,
        pin=data.pin
    )
    if not success:
        raise HTTPException(status_code=500, detail="Failed to send escrow notification emails")
    return {"success": True, "message": "Escrow notifications delivered successfully"}
