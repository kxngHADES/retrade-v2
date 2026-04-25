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
from app.db.neo4j import Neo4jConnection



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

async def add_user_to_graph(uid: str):
     driver = await Neo4jConnection.get_driver()

     async with driver.session() as session:
          await session.run(
               "CREATE (u:User {uid: $uid})",
               uid=uid
          )

async def validate_id(file_name: str, data: UploadPayload):
    async with AsyncSessionLocal() as db:
        img_path = Path(__file__).parent.parent / f"images/{file_name}"
        try:
            loop = asyncio.get_event_loop()
            id_result = await loop.run_in_executor(
                None,
                partial(extractor.extract_id_number, img_path)
            )

            await asyncio.to_thread(img_path.unlink, missing_ok=True)

            if id_result is None:
                await update_id_verification_status(data.uid, 3, db)
                return

            id_num, _ = id_result
            is_valid = IDExtractor.validate_sa_id(id_num)

            status = 1 if is_valid else 3
            await update_id_verification_status(data.uid, status, db)

            if is_valid:
                await asyncio.to_thread(img_path.unlink, missing_ok=True)
                await update_rbac(data.uid, 1, db)
                await add_user_to_graph(data.uid)

        except FileNotFoundError:
            await update_id_verification_status(data.uid, 3, db)
        except Exception:
            await update_id_verification_status(data.uid, 3, db)