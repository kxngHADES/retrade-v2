from app.core.config import settings
from app.utils.email_helper import send_email, send_sms
from pydantic import EmailStr
from app.services.verify_id import IDExtractor
from pathlib import Path



extractor = IDExtractor(gpu=False)

# Validate Email
async def validate_email(to_email: EmailStr, otp: int):
	template_name = "validate_email"
	subject = "ReTrade Verify Email Address"

	return await send_email(to_email=to_email, template_name=template_name, subject=subject, otp=otp)


# Validate Phone




# Validate ID
async def validate_id(file_name: str):
	img_path = Path(__file__).parent.parent / f"images/{file_name}"
	try:
		texts = extractor.extract_text(img_path)
		id_result = extractor.find_id_number(texts)
		if id_result is None:
			print(f"No ID number found in image: {file_name}")
			# TODO: mark id cverification as failed
			return

		id_num = id_result[0]
		is_id = extractor.validate_sa_id(id_num)
		if (is_id):
			# Update is_id_verified
			# delete the image
			pass
		else:
			pass
	
	except FileNotFoundError as e:
		print(f"Error: {e}")