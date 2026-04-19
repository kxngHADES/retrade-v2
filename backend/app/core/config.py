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

	@property
	def DATABASE_URL(self) -> str:
		return (
			f"mysql+asyncmy://{self.DB_USER}:{self.DB_PASS}"
			f"@{self.DB_HOST}:{self.DB_PORT}/{self.DB_NAME}"
			f"?charset={self.DB_CHARSET}"
		)

	model_config = SettingsConfigDict(env_file=".env", env_file_encoding="utf-8", extra="ignore")

settings = Settings()