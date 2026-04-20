from motor.motor_asyncio import AsyncIOMotorClient
from app.core.config import settings

class MongoConnection:
	_client = None
	_db = None
		
	@classmethod
	async def get_db(cls):
		if cls._client is None:
			cls._client = AsyncIOMotorClient(
				settings.MONGO_URI.get_secret_value()
			)
			cls._db = cls._client[settings.MONGO_DB_NAME]
			await cls._apply_validators_()
		return cls._db
  
	@classmethod
	async def _apply_validators_(cls):
		listing_schema = {
			"bsonType": "object",
			"required": [
				"name", "description", "price", "stock",
				"condition", "category", "location", "delivery_method"
			],
			"properties": {
				"name": {"bsonType": "string"},
				"description": {"bsonType": "string"},
				"thumbnail_url": {"bsonType": "string"},
				"list_of_image_url": {
					"bsonType": "array",
					"items": {"bsonType": "string"}
				},
				"price": {"bsonType": ["double", "int"]},
				"stock": {"bsonType": "int"},
				"condition": {"bsonType": "string"},
				"category": {"bsonType": "string"},
				"location": {"bsonType": "string"},
				"delivery_method": {"bsonType": "string"},
				"tags": {
					"bsonType": "array",
					"items": {"bsonType": "string"}
				}
			}
		}

		try:
			await cls._db.create_collection(
				"individual_listings",
				validator={"$jsonSchema": listing_schema}
			)
		except Exception:
			pass

		# Indexes (optimize common queries)
		await cls._db["individual_listings"].create_index("category")
		await cls._db["individual_listings"].create_index("price")
		await cls._db["individual_listings"].create_index("location")
		await cls._db["individual_listings"].create_index("tags")
		

db = None
individual_listings_collection = None



async def init_db():
	global db, individual_listings_collection

	await MongoConnection.get_db()

	db = MongoConnection._db
	individual_listings_collection = db.individual_listings