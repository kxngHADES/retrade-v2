from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from app.routes.auth_routes import router as auth_router
from app.core.config import settings
import uvicorn

app = FastAPI(
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

@app.get("/", tags=["health"])
async def root():
	return {"messgae": "ReTrade Backend is running"}

@app.get("/health" , tags=["health"])
async def health():
	return {"engine": "fastAPI"}

@app.on_event("startup")
async def startup_events():
	print("Starting up....")

@app.on_event("shutdown")
async def shutdown_event():
	print("Shutting down")


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