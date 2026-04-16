from app.core.config import settings
from app.utils.email_helper import send_email, send_sms
from pydantic import EmailStr
from app.services.verify_id import IDExtractor
from pathlib import Path
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import update
import uuid
from app.schemas.user import User
from app.models.auth_models import UploadPayload
from app.db.session import AsyncSessionLocal



extractor = IDExtractor(gpu=False)

# Validate Email
async def validate_email(to_email: EmailStr, otp: int):
	template_name = "validate_email"
	subject = "ReTrade Verify Email Address"

	return await send_email(to_email=to_email, template_name=template_name, subject=subject, otp=otp)


# Validate Phone




# Validate ID

async def update_id_verification_status(uid_str: str, status: int, db: AsyncSession) -> bool:
	try:
		uid_bytes = uuid.UUID(uid_str).bytes
	except ValueError:
		return False
	
	stmt = update(User).where(User.uid == uid_bytes).values(is_id_verified=status)
	result = await db.execute(stmt)
	await db.commit()
	return result.rowcount > 0


async def validate_id(file_name: str, data: UploadPayload):
	async with AsyncSessionLocal() as db:
		img_path = Path(__file__).parent.parent / f"images/{file_name}"
		try:
			texts = extractor.extract_text(img_path)
			id_result = extractor.find_id_number(texts)
			if id_result is None:
				print(f"No ID number found in image: {file_name}")
				await update_id_verification_status(data.uid, 3, db)
				return

			id_num = id_result[0]
			is_id = extractor.validate_sa_id(id_num)
			if (is_id):
				await update_id_verification_status(data.uid, 1, db)
				# delete the image
				pass
			else:
				await update_id_verification_status(data.uid, 3, db)
				pass
		
		except FileNotFoundError as e:
			print(f"Error: {e}")
			await update_id_verification_status(data.uid, 3, db)