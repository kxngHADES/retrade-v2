from app.core.config import settings
from app.utils.email_helper import send_sms
from app.models.auth_models import OTP_model

async def send_otp(data: OTP_model):
	message = f"ReTrade. Never share thos One Time PIN with anyone use \n\nOTP: {data.otp} to verify your phone numer"
	return await send_sms(data.phone, message)