from pydantic import BaseModel, EmailStr


class OTP_model(BaseModel):
	phone: str
	otp: int

class Email_verification(BaseModel):
	email: EmailStr
	otp: int

class UploadPayload(BaseModel):
	filename: str
	uid: str