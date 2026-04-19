from fastapi import FastAPI, Depends
from fastapi.middleware.cors import CORSMiddleware
from app.routes.auth_routes import router as auth_router
from app.core.config import settings
import uvicorn
from sqlalchemy.ext.asyncio import AsyncSession
from app.db.session import engine, get_db
from contextlib import asynccontextmanager
import app.schemas
from app.db.base import Base
from sqlalchemy import text
from app.db.mongodb import init_db




@asynccontextmanager
async def lifespan(app: FastAPI):
	async with engine.begin() as conn:
		await conn.run_sync(Base.metadata.create_all)
	await init_db()
	yield
	await engine.dispose()

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