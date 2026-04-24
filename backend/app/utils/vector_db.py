import uuid
import httpx
from qdrant_client import QdrantClient, AsyncQdrantClient
from qdrant_client.models import VectorParams, Distance, PointStruct, Filter, FieldCondition, MatchValue

from app.core.config import settings



# Ollama embedding
async def get_embedding(text: str) -> list[float]:
    async with httpx.AsyncClient() as client:
        res = await client.post(
            f"{settings.OLLAMA_BASE_URL}/api/embeddings",
            json={
                "model": settings.OLLAMA_MODEL,
                "prompt": text
            }
        )
        res.raise_for_status()
        return res.json()["embedding"]


# Qdrant Clients (Singleton instances)
_qdrant_client: QdrantClient | None = None
_async_qdrant_client: AsyncQdrantClient | None = None

def get_qdrant_client() -> QdrantClient:
    global _qdrant_client
    if _qdrant_client is None:
        _qdrant_client = QdrantClient(
            url=settings.QDRANT_URL,
            api_key=settings.QDRANT_API_KEY.get_secret_value(),
            prefer_grpc=False
        )
    return _qdrant_client

async def get_async_qdrant_client() -> AsyncQdrantClient:
    global _async_qdrant_client
    if _async_qdrant_client is None:
        _async_qdrant_client = AsyncQdrantClient(
            url=settings.QDRANT_URL,
            api_key=settings.QDRANT_API_KEY.get_secret_value(),
            prefer_grpc=True
        )
    return _async_qdrant_client

async def close_qdrant_clients():
    global _qdrant_client, _async_qdrant_client
    if _qdrant_client is not None:
        _qdrant_client.close()
        _qdrant_client = None
    if _async_qdrant_client is not None:
        await _async_qdrant_client.close()
        _async_qdrant_client = None

# Collection setup
def ensure_collection_exists(client: QdrantClient, collection_name: str, vector_size: int, distance: str):
    # Map string distance to qdrant Distance enum
    distance_map = {
        "COSINE": Distance.COSINE,
        "EUCLID": Distance.EUCLID,
        "DOT": Distance.DOT
    }
    dist = distance_map.get(distance.upper(), Distance.COSINE)

    if not client.collection_exists(collection_name):
        client.create_collection(
            collection_name=collection_name,
            vectors_config=VectorParams(size=vector_size, distance=dist)
        )
          


# Insert Listing embedding
async def add_listing_to_vector_db(mongo_listing_id: str, text_to_embed: str, payload: dict) -> str:
    vector = await get_embedding(text_to_embed)
    client = await get_async_qdrant_client()

    qdrant_point_id = str(uuid.uuid4())

    payload_with_ref = {**payload, "mongo_id": mongo_listing_id}

    point = PointStruct(
        id=qdrant_point_id,
        vector=vector,
        payload=payload_with_ref
    )

    await client.upsert(
        collection_name=settings.QDRANT_COLLECTION,
        points=[point],
        wait=True
    )

    return qdrant_point_id




# Searh similar listings
async def search_similar_listings(
    query_text: str,
    filter_by: dict | None = None,
    limit: int = 10
) -> list[dict]:
    #q. Embed the query
    query_vector = await get_embedding(query_text)
    
    # 2. Build filter if needed
    qdrant_filter = None
    if filter_by:
        conditions = [
            FieldCondition(key=key, match=MatchValue(value=value))
            for key, value in filter_by.items()
        ]
        qdrant_filter = Filter(must=conditions)
    
    # 3. Search
    client = await get_async_qdrant_client()
    
    results = await client.query_points(
        collection_name=settings.QDRANT_COLLECTION,
        query=query_vector,
        query_filter=qdrant_filter,
        with_payload=True,
        limit=limit
    )
    
    return [point.payload for point in results.points]