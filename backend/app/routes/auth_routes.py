from fastapi import APIRouter, BackgroundTasks, HTTPException
from app.services.Auth_services import send_otp
from app.models.auth_models import OTP_model

router = APIRouter(prefix="/auth", tags=["Authentication"])

@router.post("/send-otp", status_code=200)
async def send_otp_endpoint(otp_data: OTP_model, background_tasks: BackgroundTasks):
	background_tasks.add_task(send_otp, otp_data)
	return {"success": True, "message": "OTP is being sent"}