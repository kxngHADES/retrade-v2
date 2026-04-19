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
import asyncio
from functools import partial



extractor = IDExtractor()

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

async def update_rbac(uid_str: str, status:int, db: AsyncSession) -> bool:
     try:
          uid_bytes = uuid.UUID(uid_str).bytes
     except ValueError:
          return False
     
     stmt = update(User).where(User.uid == uid_bytes).values(rbac_role=status)
     result = await db.execute(stmt)
     await db.commit()
     return result.rowcount > 0


async def validate_id(file_name: str, data: UploadPayload):
    async with AsyncSessionLocal() as db:
        img_path = Path(__file__).parent.parent / f"images/{file_name}"
        try:
            loop = asyncio.get_event_loop()
            id_result = await loop.run_in_executor(
                None,
                partial(extractor.extract_id_number, img_path)
            )

            img_path.unlink(missing_ok=True)

            if id_result is None:
                print(f"No ID number found in image: {file_name}")
                await update_id_verification_status(data.uid, 3, db)
                return

            id_num, _ = id_result
            is_valid = IDExtractor.validate_sa_id(id_num)

            status = 1 if is_valid else 3
            await update_id_verification_status(data.uid, status, db)

            if is_valid:
                img_path.unlink(missing_ok=True)
                await update_rbac(data.uid, 1, db)

        except FileNotFoundError as e:
            print(f"Image not found: {e}")
            await update_id_verification_status(data.uid, 3, db)
        except Exception as e:
            print(f"Unexpected error during ID validation: {e}")
            await update_id_verification_status(data.uid, 3, db)