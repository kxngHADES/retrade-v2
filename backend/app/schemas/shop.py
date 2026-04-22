from sqlalchemy import (
	String, LargeBinary, SmallInteger, ForeignKey, func
)
from sqlalchemy.orm import Mapped, mapped_column, relationship
from app.db.base import Base


class Shop(Base):
	__tablename__ = "shops"

	shop_id: Mapped[bytes] = mapped_column(
		LargeBinary(16),
		primary_key=True,
		server_default=func.uuid_to_bin(func.uuid())
	)

	uid: Mapped[bytes] = mapped_column(
		LargeBinary(16),
		ForeignKey("users.uid", ondelete="CASCADE", onupdate="CASCADE"),
		nullable=False
	)

	shop_name: Mapped[str] = mapped_column(
		String(255),
		nullable=False
	)

	status: Mapped[int] = mapped_column(
		SmallInteger,
		nullable=False,
		server_default="0"
	)

	user: Mapped["User"] = relationship(
		"User",
		back_populates="shops"
	)