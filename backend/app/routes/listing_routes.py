from fastapi import APIRouter
from app.services.listing_services import create_listing, get_users_listings
from app.models.listing_models import IndividualListing, individual

router = APIRouter(prefix="/listings", tags=["Listings"])


@router.get("/get_user_listings")
async def get_user_listings(data: individual):
    listings = await get_users_listings(data)
    return {"success":True, "data": listings}


@router.post("/create_user_listing")
async def create_user_listings(data: IndividualListing):
    return create_listing(data)