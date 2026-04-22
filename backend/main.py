from fastapi import FastAPI, Depends
from fastapi.middleware.cors import CORSMiddleware
from app.routes.auth_routes import router as auth_router
from app.routes.listing_routes import router as listing_router
from app.core.config import settings
import uvicorn
from sqlalchemy.ext.asyncio import AsyncSession
from app.db.session import engine, get_db
from contextlib import asynccontextmanager
import app.schemas
from app.db.base import Base
from sqlalchemy import text
from app.db.mongodb import init_db
from app.db.neo4j import Neo4jConnection
from app.utils.vector_db import get_qdrant_client, ensure_collection_exists
from app.db.redis_db import init_redis, close_redis




@asynccontextmanager
async def lifespan(app: FastAPI):
	async with engine.begin() as conn:
		await conn.run_sync(Base.metadata.create_all)
	await init_db() # MongoDB
	await Neo4jConnection.connect() # Neo4j
	await init_redis()
	client = get_qdrant_client() # Qdrant
	try:
		ensure_collection_exists(
			client=client,
			collection_name=settings.QDRANT_COLLECTION,
			vector_size=settings.QDRANT_VECTOR_SIZE,
			distance=settings.QDRANT_DISTANCE
		)
	except Exception as e:
		print(f"⚠️ Qdrant startup warning: {e}")
	yield
	await engine.dispose()
	await Neo4jConnection.close()
	await close_redis()

app = FastAPI(
	lifespan=lifespan,
	title="ReTrade Authentication API and engine",
	description="API/Engine for ReTrade",
	version="0.0.1"
)

env_type = settings.ENVIRONMENT

if env_type == "dev":
	app.add_middleware(
		CORSMiddleware,
		allow_origins=["*"],
		allow_credentials=True,
		allow_methods=["*"],
		allow_headers=["*"],
	)
else:
	app.add_middleware(
		CORSMiddleware,
		allow_origins=[settings.Domains],
		allow_credentials=True,
		allow_methods=["*"],
		allow_headers=["*"],
	)



# Routes
app.include_router(auth_router)
app.include_router(listing_router)

@app.get("/ping")
async def ping_db(db: AsyncSession = Depends(get_db)):
	result = await db.execute(text("SELECT 1"))
	return {"status": "ok", "result": result.scalar_one()}

@app.get("/", tags=["health"])
async def root():
	return {"messgae": "ReTrade Backend is running"}

@app.get("/health" , tags=["health"])
async def health():
	return {"engine": "fastAPI"}


if __name__ == "__main__":
	if env_type == "dev":
		uvicorn.run(
			"main:app",
			host="127.0.0.1",
			port=8000,
			reload=True
		)
	else:
		uvicorn.run(
			"main:app",
			host="0.0.0.0",
			port=8000,
			reload=True
		)