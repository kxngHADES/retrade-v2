from pydantic_settings import BaseSettings, SettingsConfigDict
from pydantic import SecretStr

class Settings(BaseSettings):
	CARRIER_GATEWAY: str = "winsms.net"

	SMTP_HOST: str | None = None
	SMTP_PORT: int | None = None
	SMTP_USER: str | None = None
	SMTP_PASS: SecretStr | None = None

	model_config = SettingsConfigDict(env_file=".env")

settings = Settings()