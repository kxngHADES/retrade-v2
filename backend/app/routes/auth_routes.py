from fastapi import APIRouter, BackgroundTasks, HTTPException, File, UploadFile, Form, Depends
from app.services.Auth_services import send_otp
from app.services.validate_users import validate_email, validate_id
from app.models.auth_models import OTP_model, Email_verification, UploadPayload
from pathlib import Path

router = APIRouter(prefix="/auth", tags=["Authentication"])
IMG_DIR = Path(__file__).parent.parent / "images"
IMG_DIR.mkdir(parents=True, exist_ok=True)

@router.post("/send-otp", status_code=200)
async def send_otp_endpoint(otp_data: OTP_model, background_tasks: BackgroundTasks):
	background_tasks.add_task(send_otp, otp_data)
	return {"success": True, "message": "OTP is being sent"}


@router.post("/validate-email", status_code=200)
async def send_email_otp_endpoint(data:Email_verification, background_tasks: BackgroundTasks):
	background_tasks.add_task(validate_email, data.email, data.otp)
	return {"success": True, "message": "OTP is being sent"}


def get_upload_payload(filename: str = Form(...)):
	return UploadPayload(filename=filename)

@router.post("/validate_id", status_code=202)
async def upload_id(
	background_tasks: BackgroundTasks,
	payload: UploadPayload = Depends(get_upload_payload),
	file: UploadFile = File(..., description="The image file to upload")):

	ext = Path(file.filename or ".jpg").suffix
	final_filename = f"{payload.filename}{ext}"

	file_path = IMG_DIR / final_filename
	contents = await file.read()

	with open(file_path, "wb") as f:
		f.write(contents)

	background_tasks.add_task(validate_id, final_filename)
	return {"success": True,
					"message": "Validating Identity"}