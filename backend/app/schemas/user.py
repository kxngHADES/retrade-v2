from sqlalchemy import (
	String, LargeBinary, Integer, Boolean, DateTime, func,
	SmallInteger, Index
)
from sqlalchemy.orm import Mapped, mapped_column, relationship
from geoalchemy2 import Geometry
from datetime import datetime
from app.db.base import Base

class User(Base):
	__tablename__ = "users"

	uid: Mapped[bytes] = mapped_column(
		LargeBinary(16),
		primary_key=True,
		default=lambda: func.uuid_to_bin(func.uuid()),
		server_default=func.uuid_to_bin(func.uuid())
	)
	firstName: Mapped[str] = mapped_column(String(255), nullable=False)
	lastName: Mapped[str] = mapped_column(String(255), nullable=False)
	email: Mapped[str] = mapped_column(String(255), nullable=False, unique=True)
	phoneNumber: Mapped[str] = mapped_column(String(20), nullable=False, unique=True)
	password: Mapped[str] = mapped_column(String(255), nullable=False)
	is_phone_verified: Mapped[int] = mapped_column(
		SmallInteger,
		nullable=False,
		server_default="0"
	)
	is_email_verified: Mapped[int] = mapped_column(
		SmallInteger,
		nullable=False,
		server_default="0"
	)
	is_id_verified: Mapped[int] = mapped_column(
		SmallInteger,
		nullable=False,
		server_default="0",
		comment="0=false, 1=true, 2=pending, 4=failed"
	)
	profile_image_url: Mapped[str | None] = mapped_column(String(2048), nullable=True)
	rbac_role: Mapped[int] = mapped_column(
		SmallInteger, nullable=False, server_default="0",
		comment="0=buy only, 1=buy/sell, 2=admin, 3=superAdmin"
	)
	is_banned: Mapped[bool] = mapped_column(
		Boolean, nullable=False, server_default="0"
	)
	province: Mapped[str | None] = mapped_column(String(120), nullable=True)
	city: Mapped[str | None] = mapped_column(String(255), nullable=True)
	street_address: Mapped[str | None] = mapped_column(String(255), nullable=True)
	location_point: Mapped[bytes | None] = mapped_column(
		Geometry('POINT', srid=4326),   # Spatial column
		nullable=True
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

	shops: Mapped[list["Shop"]] = relationship(
		"Shop",
		back_populates="user",
		cascade="all, delete-orphan"
	)

	__table_args__ = (
		Index('idx_users_location_point', 'location_point', postgresql_using='gist'),
	)
	
	carts: Mapped[list["Cart"]] = relationship(
		"Cart",
		back_populates="user",
		cascade="all, delete-orphan"
	)