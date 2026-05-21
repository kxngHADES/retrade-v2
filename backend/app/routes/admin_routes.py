from fastapi import APIRouter, BackgroundTasks, Depends, HTTPException
from app.utils.email_helper import send_mass_email
from app.db.session import get_db
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import text
from pydantic import BaseModel

router = APIRouter(prefix="/admin/broadcast", tags=["Admin Broadcast"])

class BroadcastRequest(BaseModel):
    subject: str
    content: str
    batch_size: int = 20

@router.post("/send")
async def broadcast_email(request: BroadcastRequest, background_tasks: BackgroundTasks, db: AsyncSession = Depends(get_db)):
    # Get all user emails
    result = await db.execute(text("SELECT email FROM users"))
    emails = [row[0] for row in result.all()]
    
    if not emails:
        return {"message": "No users found."}

    # Chunking logic for background processing
    for i in range(0, len(emails), request.batch_size):
        batch = emails[i : i + request.batch_size]
        background_tasks.add_task(send_mass_email, batch, request.subject, request.content)

    return {"message": f"Broadcast started for {len(emails)} users in batches of {request.batch_size}."}
