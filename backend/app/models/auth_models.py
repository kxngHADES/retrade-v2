from pydantic import BaseModel


class OTP_model(BaseModel):
	phone: str
	otp: int