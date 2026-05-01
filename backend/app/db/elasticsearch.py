from elasticsearch import AsyncElasticsearch
from app.core.config import settings

class ElasticsearchConnection:
    _client: AsyncElasticsearch | None = None

    @classmethod
    async def connect(cls):
        if not cls._client:
            cls._client = AsyncElasticsearch(
                hosts=[settings.ELASTICSEARCH_URL],
                verify_certs=False,
            )
            # Create index if it doesn't exist
            try:
                exists = await cls._client.indices.exists(index=settings.ELASTICSEARCH_INDEX)
                if not exists:
                    await cls._client.indices.create(index=settings.ELASTICSEARCH_INDEX)
                    print(f"✅ Created Elasticsearch index: {settings.ELASTICSEARCH_INDEX}")
            except Exception as e:
                print(f"⚠️ Could not create Elasticsearch index: {e}")

    @classmethod
    async def close(cls):
        if cls._client:
            await cls._client.close()
            cls._client = None

    @classmethod
    def get_client(cls) -> AsyncElasticsearch:
        if not cls._client:
            raise RuntimeError("Elasticsearch client is not initialized.")
        return cls._client
