from sqlalchemy import (
	String, BINARY, SmallInteger, ForeignKey, func, Text, Integer, Numeric
)
from sqlalchemy.orm import Mapped, mapped_column, relationship
from app.db.base import Base


class Shop(Base):
	__tablename__ = "shops"

	shop_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		primary_key=True,
		server_default=func.uuid_to_bin(func.uuid())
	)

	uid: Mapped[bytes] = mapped_column(
		BINARY(16),
		ForeignKey("users.uid", ondelete="CASCADE", onupdate="CASCADE"),
		nullable=False
	)

	shop_name: Mapped[str] = mapped_column(
		String(255),
		nullable=False,
		unique=True
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
	products: Mapped[list["Shop_products"]] = relationship(
		"Shop_products",
		back_populates="shop",
		cascade="all, delete-orphan"
	)

	carts: Mapped[list["Cart"]] = relationship(
		"Cart",
		back_populates="shop",
		cascade="all, delete-orphan"
	)











class Shop_products(Base):
	__tablename__ = "shop_products"

	product_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		primary_key=True,
		server_default=func.uuid_to_bin(func.uuid())
	)

	shop_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		ForeignKey("shops.shop_id", ondelete="CASCADE", onupdate="CASCADE"),
		nullable=False
	)

	name: Mapped[str] = mapped_column(
		String(255),
		nullable=False
	)

	description: Mapped[str] = mapped_column(
		Text,
		nullable=False
	)

	stock_quantity: Mapped[int] = mapped_column(
		Integer,
		nullable=False
	)

	price: Mapped[float] = mapped_column(
		Numeric(10, 2),
		nullable=False,
		default=0.00
	)

	is_active: Mapped[int] = mapped_column(
		SmallInteger,
		nullable=False
	)

	shop: Mapped["Shop"] = relationship(
		"Shop",
		back_populates="products"
	)