from sqlalchemy import String, BINARY, DateTime, func, Text, ForeignKey
from sqlalchemy.orm import Mapped, mapped_column, relationship
from datetime import datetime
from app.db.base import Base

class UserReport(Base):
	__tablename__ = "user_reports"

	report_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		primary_key=True,
		default=lambda: func.uuid_to_bin(func.uuid()),
		server_default=func.uuid_to_bin(func.uuid())
	)
	reporter_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		ForeignKey('users.uid', ondelete='CASCADE'),
		nullable=False
	)
	report_type: Mapped[str] = mapped_column(String(50), nullable=False)
	target_reference_id: Mapped[str | None] = mapped_column(String(255), nullable=True)
	reason: Mapped[str] = mapped_column(String(255), nullable=False)
	description: Mapped[str | None] = mapped_column(Text, nullable=True)
	status: Mapped[str] = mapped_column(
		String(50), nullable=False, server_default="pending"
	)
	admin_notes: Mapped[str | None] = mapped_column(Text, nullable=True)
	
	created_at: Mapped[datetime] = mapped_column(
		DateTime, nullable=False, server_default=func.current_timestamp()
	)
	updated_at: Mapped[datetime] = mapped_column(
		DateTime, nullable=False,
		server_default=func.current_timestamp(),
		onupdate=func.current_timestamp()
	)
