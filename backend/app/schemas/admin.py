from sqlalchemy import String, BINARY, Boolean, DateTime, func, SmallInteger
from sqlalchemy.orm import Mapped, mapped_column
from datetime import datetime
from app.db.base import Base

class Admin(Base):
	__tablename__ = "admins"

	admin_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		primary_key=True,
		default=lambda: func.uuid_to_bin(func.uuid()),
		server_default=func.uuid_to_bin(func.uuid())
	)
	firstName: Mapped[str] = mapped_column(String(255), nullable=False)
	lastName: Mapped[str] = mapped_column(String(255), nullable=False)
	email: Mapped[str] = mapped_column(String(255), nullable=False, unique=True)
	password: Mapped[str] = mapped_column(String(255), nullable=False)
	profile_image_url: Mapped[str | None] = mapped_column(String(2048), nullable=True)
	rbac_role: Mapped[int] = mapped_column(
		SmallInteger,
		nullable=False,
		server_default="2",
		comment="2=admin, 3=superAdmin"
	)
	is_active: Mapped[bool] = mapped_column(
		Boolean, nullable=False, server_default="1"
	)
	created_at: Mapped[datetime] = mapped_column(
		DateTime, nullable=False, server_default=func.current_timestamp()
	)
	updated_at: Mapped[datetime] = mapped_column(
		DateTime, nullable=False,
		server_default=func.current_timestamp(),
		onupdate=func.current_timestamp()
	)
	last_login: Mapped[datetime | None] = mapped_column(DateTime, nullable=True)
