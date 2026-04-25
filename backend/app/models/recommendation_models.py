from pydantic import BaseModel
from typing import Optional, List

class UserView(BaseModel):
    uid: str
    listing_id: str

class RecommendationRequest(BaseModel):
    uid: str
    limit: int = 50
