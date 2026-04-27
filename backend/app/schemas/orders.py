from sqlalchemy import (
    BINARY, ForeignKey, DateTime, String, SmallInteger, Numeric, func, CheckConstraint, Index
)
from sqlalchemy.orm import Mapped, mapped_column, relationship
from datetime import datetime
from app.db.base import Base

class Order(Base):
    __tablename__ = "orders"

    order_id: Mapped[bytes] = mapped_column(
        BINARY(16),
        primary_key=True,
        server_default=func.uuid_to_bin(func.uuid())
    )

    buyer_uid: Mapped[bytes] = mapped_column(
        BINARY(16),
        ForeignKey("users.uid", ondelete="CASCADE", onupdate="CASCADE"),
        nullable=False
    )

    seller_uid: Mapped[bytes] = mapped_column(
        BINARY(16),
        ForeignKey("users.uid", ondelete="CASCADE", onupdate="CASCADE"),
        nullable=False
    )

    shop_id: Mapped[bytes | None] = mapped_column(
        BINARY(16),
        ForeignKey("shops.shop_id", ondelete="SET NULL", onupdate="CASCADE"),
        nullable=True
    )

    cart_id: Mapped[bytes | None] = mapped_column(
        BINARY(16),
        ForeignKey("carts.cart_id", ondelete="SET NULL", onupdate="CASCADE"),
        nullable=True
    )

    listing_id: Mapped[str | None] = mapped_column(
        String(64),
        nullable=True,
        comment="MongoDB ObjectId for individual marketplace listings"
    )

    order_type: Mapped[str] = mapped_column(
        String(32),
        nullable=False,
        comment="marketplace or shop"
    )

    total_amount: Mapped[float] = mapped_column(
        Numeric(12, 2),
        nullable=False,
        server_default="0.00"
    )

    status: Mapped[int] = mapped_column(
        SmallInteger,
        nullable=False,
        server_default="0",
        comment="0=pending, 1=paid, 2=shipped, 3=completed, 4=cancelled"
    )

    created_at: Mapped[datetime] = mapped_column(
        DateTime,
        nullable=False,
        server_default=func.current_timestamp()
    )

    updated_at: Mapped[datetime] = mapped_column(
        DateTime,
        nullable=False,
        server_default=func.current_timestamp(),
        onupdate=func.current_timestamp()
    )

    # Relationships
    buyer: Mapped["User"] = relationship("User", foreign_keys=[buyer_uid])
    seller: Mapped["User"] = relationship("User", foreign_keys=[seller_uid])
    shop: Mapped["Shop"] = relationship("Shop", foreign_keys=[shop_id])
    cart: Mapped["Cart"] = relationship("Cart", foreign_keys=[cart_id])
    payments: Mapped[list["Payment"]] = relationship(
        "Payment",
        back_populates="order",
        cascade="all, delete-orphan"
    )

    __table_args__ = (
        Index("idx_orders_buyer", "buyer_uid"),
        Index("idx_orders_seller", "seller_uid"),
        Index("idx_orders_shop", "shop_id"),
        Index("idx_orders_cart", "cart_id"),
        Index("idx_orders_listing", "listing_id"),
    )
