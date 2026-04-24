from sqlalchemy import (
	BINARY, SmallInteger, ForeignKey, DateTime,
	Integer, Numeric, UniqueConstraint, Index, func, case
)
from sqlalchemy.orm import Mapped, mapped_column, relationship
from sqlalchemy.dialects.mysql import VARBINARY
from sqlalchemy.sql import expression
from sqlalchemy import Computed
from app.db.base import Base


class Cart(Base):
	__tablename__ = "carts"

	cart_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		primary_key=True,
		server_default=func.uuid_to_bin(func.uuid())
	)

	uid: Mapped[bytes] = mapped_column(
		BINARY(16),
		ForeignKey("users.uid", ondelete="CASCADE", onupdate="CASCADE"),
		nullable=False
	)

	shop_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		ForeignKey("shops.shop_id", ondelete="CASCADE", onupdate="CASCADE"),
		nullable=False
	)

	status: Mapped[int] = mapped_column(
		SmallInteger,
		nullable=False,
		server_default="1"
	)

	active_cart_key: Mapped[bytes | None] = mapped_column(
        VARBINARY(32),
        Computed(
            "CASE WHEN status = 1 THEN CONCAT(uid, shop_id) ELSE NULL END",
            persisted=True
        )
    )

	updated_at: Mapped[str] = mapped_column(
		DateTime,
		nullable=False,
		server_default=func.current_timestamp(),
		onupdate=func.current_timestamp()
	)

	user: Mapped["User"] = relationship(
		"User",
		back_populates="carts"
	)

	shop: Mapped["Shop"] = relationship(
		"Shop",
		back_populates="carts"
	)

	items: Mapped[list["CartItem"]] = relationship(
		"CartItem",
		back_populates="cart",
		cascade="all, delete-orphan"
	)

	__table_args__ = (
		UniqueConstraint("active_cart_key", name="unique_active_cart"),
		Index("idx_carts_uid", "uid"),
		Index("idx_carts_shop", "shop_id"),
	)







class CartItem(Base):
	__tablename__ = "cart_items"

	item_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		primary_key=True,
		server_default=func.uuid_to_bin(func.uuid())
	)

	cart_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		ForeignKey("carts.cart_id", ondelete="CASCADE", onupdate="CASCADE"),
		nullable=False
	)

	shop_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		ForeignKey("shops.shop_id", ondelete="CASCADE", onupdate="CASCADE"),
		nullable=False
	)

	product_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		ForeignKey("shop_products.product_id", ondelete="CASCADE", onupdate="CASCADE"),
		nullable=False
	)

	quantity: Mapped[int] = mapped_column(
		Integer,
		nullable=False,
		server_default="1"
	)

	price_snapshot: Mapped[float] = mapped_column(
		Numeric(10, 2),
		nullable=False,
		server_default="0.00"
	)

	# Relationships
	cart: Mapped["Cart"] = relationship(
		"Cart",
		back_populates="items"
	)

	product: Mapped["Shop_products"] = relationship(
		"Shop_products"
	)

	shop: Mapped["Shop"] = relationship(
		"Shop"
	)

	__table_args__ = (
		UniqueConstraint("cart_id", "product_id", name="uq_cart_product"),
		Index("idx_items_cart", "cart_id"),
		Index("idx_items_product", "product_id"),
		Index("idx_items_shop", "shop_id"),
	)