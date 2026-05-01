from pydantic_settings import BaseSettings, SettingsConfigDict
from pydantic import SecretStr

class Settings(BaseSettings):
	CARRIER_GATEWAY: str = "winsms.net"

	SMTP_HOST: str | None = None
	SMTP_PORT: int | None = None
	SMTP_USER: str | None = None
	SMTP_PASS: SecretStr | None = None

	ENVIRONMENT: str
	Domains: str

	DB_HOST: str
	DB_PORT: int
	DB_NAME: str
	DB_USER: str
	DB_PASS: str
	DB_CHARSET: str

	MONGO_URI: SecretStr
	MONGO_DB_NAME: str

	NEO4J_URI: str
	NEO4J_USER: str
	NEO4J_PASSWORD: SecretStr

	QDRANT_HOST: str
	QDRANT_PORT: int
	QDRANT_API_KEY: SecretStr
	QDRANT_COLLECTION: str
	QDRANT_VECTOR_SIZE: int
	QDRANT_DISTANCE: str

	ELASTICSEARCH_HOST: str
	ELASTICSEARCH_PORT: int
	ELASTICSEARCH_INDEX: str

	OLLAMA_BASE_URL: str
	OLLAMA_MODEL: str

	REDIS_HOST: str
	REDIS_PORT: int
	REDIS_DB: int
	REDIS_CACHE_TTL: int


	@property
	def DATABASE_URL(self) -> str:
		return (
			f"mysql+asyncmy://{self.DB_USER}:{self.DB_PASS}"
			f"@{self.DB_HOST}:{self.DB_PORT}/{self.DB_NAME}"
			f"?charset={self.DB_CHARSET}"
		)
	
	@property
	def QDRANT_URL(self) -> str:
		return f"http://{self.QDRANT_HOST}:{self.QDRANT_PORT}"

	@property
	def ELASTICSEARCH_URL(self) -> str:
		return f"http://{self.ELASTICSEARCH_HOST}:{self.ELASTICSEARCH_PORT}"
	
	@property
	def REDIS_URL(self) -> str:
		return f"redis://{self.REDIS_HOST}:{self.REDIS_PORT}/{self.REDIS_DB}"

	model_config = SettingsConfigDict(env_file=".env", env_file_encoding="utf-8", extra="ignore")

settings = Settings()