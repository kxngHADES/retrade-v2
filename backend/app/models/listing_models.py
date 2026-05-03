from pydantic import BaseModel, Field
from typing import List, Optional

class individual(BaseModel):
    uid: str

class IndividualListing(BaseModel):
    uid: str
    name: str
    description: str

    thumbnail_url: Optional[str] = None
    list_of_image_url: List[str] = []

    price: float
    stock: int

    condition: str
    category: str
    location: str
    delivery_method: Optional[str] = None

    tags: list[str] = []

class IndividualListingUpdate(BaseModel):
    name: Optional[str] = None
    description: Optional[str] = None
    thumbnail_url: Optional[str] = None
    list_of_image_url: Optional[List[str]] = None
    price: Optional[float] = None
    stock: Optional[int] = None
    condition: Optional[str] = None
    category: Optional[str] = None
    location: Optional[str] = None
    delivery_method: Optional[str] = None
    tags: Optional[List[str]] = None

class ListingSearchParams(BaseModel):
    query: str
    category: Optional[str] = None
    condition: Optional[str] = None
    location: Optional[str] = None
    min_price: Optional[float] = None
    max_price: Optional[float] = None
