from neo4j import AsyncGraphDatabase
from app.core.config import settings


class Neo4jConnection:
	_driver = None

	@classmethod
	async def connect(cls):
		if cls._driver is None:
			cls._driver = AsyncGraphDatabase.driver(
				settings.NEO4J_URI,
				auth=(
					settings.NEO4J_USER,
					settings.NEO4J_PASSWORD.get_secret_value()
				)
			)

	@classmethod
	async def get_driver(cls):
		if cls._driver is None:
			await cls.connect()
		return cls._driver

	@classmethod
	async def close(cls):
		if cls._driver:
			await cls._driver.close()
			cls._driver = None