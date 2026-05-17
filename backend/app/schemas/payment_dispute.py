from sqlalchemy import String, LargeBinary, DateTime, func, Text, ForeignKey, JSON
from sqlalchemy.orm import Mapped, mapped_column, relationship
from datetime import datetime
from app.db.base import Base
from typing import Any

class PaymentDispute(Base):
	__tablename__ = "payment_disputes"

	dispute_id: Mapped[bytes] = mapped_column(
		LargeBinary(16),
		primary_key=True,
		default=lambda: func.uuid_to_bin(func.uuid()),
		server_default=func.uuid_to_bin(func.uuid())
	)
	reporter_id: Mapped[bytes] = mapped_column(
		LargeBinary(16),
		ForeignKey('users.uid', ondelete='CASCADE'),
		nullable=False
	)
	order_id: Mapped[bytes] = mapped_column(
		LargeBinary(16),
		ForeignKey('orders.order_id', ondelete='CASCADE'),
		nullable=False
	)
	payment_reference: Mapped[str | None] = mapped_column(String(255), nullable=True)
	dispute_reason: Mapped[str] = mapped_column(String(255), nullable=False)
	description: Mapped[str] = mapped_column(Text, nullable=False)
	evidence_urls: Mapped[list[Any] | dict[str, Any] | None] = mapped_column(JSON, nullable=True)
	status: Mapped[str] = mapped_column(
		String(50), nullable=False, server_default="open"
	)
	admin_resolution_notes: Mapped[str | None] = mapped_column(Text, nullable=True)
	
	created_at: Mapped[datetime] = mapped_column(
		DateTime, nullable=False, server_default=func.current_timestamp()
	)
	updated_at: Mapped[datetime] = mapped_column(
		DateTime, nullable=False,
		server_default=func.current_timestamp(),
		onupdate=func.current_timestamp()
	)
