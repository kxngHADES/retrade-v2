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