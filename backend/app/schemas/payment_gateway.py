from datetime import datetime

from sqlalchemy import (
    BINARY,
    Boolean,
    CheckConstraint,
    DateTime,
    ForeignKey,
    Index,
    Integer,
    Numeric,
    String,
    UniqueConstraint,
    func,
)
from sqlalchemy.dialects.mysql import JSON
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.db.base import Base


class Payment(Base):
    __tablename__ = "payment"

    payment_id: Mapped[bytes] = mapped_column(
        BINARY(16),
        primary_key=True,
        server_default=func.uuid_to_bin(func.uuid())
    )

    order_id: Mapped[bytes] = mapped_column(
        BINARY(16),
        ForeignKey("orders.order_id", ondelete="CASCADE", onupdate="CASCADE"),
        nullable=False,
    )

    status: Mapped[int] = mapped_column(
        Integer,
        nullable=False,
        server_default="0",
        comment="0=pending, 1=success, 2=failed",
    )

    amount: Mapped[float] = mapped_column(
        Numeric(12, 2),
        nullable=False,
    )

    reference: Mapped[str] = mapped_column(
        String(255),
        nullable=False,
        unique=True,
    )

    paid_at: Mapped[datetime | None] = mapped_column(
        DateTime,
        nullable=True,
    )

    pin: Mapped[str] = mapped_column(
        String(5),
        nullable=False,
    )

    order: Mapped["Order"] = relationship(
        "Order",
        back_populates="payments",
        foreign_keys=[order_id],
    )

    __table_args__ = (
        UniqueConstraint("reference", name="uq_payment_reference"),
        CheckConstraint("pin REGEXP '^[0-9]{5}$'", name="chk_payment_pin"),
        Index("idx_payment_order", "order_id"),
    )


class PaymentSession(Base):
    __tablename__ = "payment_sessions"

    paymentSession_id: Mapped[int] = mapped_column(
        Integer,
        primary_key=True,
        autoincrement=True,
    )

    session_token: Mapped[str] = mapped_column(
        String(64),
        nullable=False,
        unique=True,
    )

    user_email: Mapped[str] = mapped_column(
        String(255),
        nullable=False,
    )

    amount: Mapped[float] = mapped_column(
        Numeric(12, 2),
        nullable=False,
    )

    status: Mapped[str] = mapped_column(
        String(20),
        nullable=False,
        server_default="pending",
        comment="pending, processing, success, failed, expired",
    )

    webhook_delivered: Mapped[bool] = mapped_column(
        Boolean,
        nullable=False,
        server_default="0",
    )

    webhook_attempts: Mapped[int] = mapped_column(
        Integer,
        nullable=False,
        server_default="0",
    )

    created_at: Mapped[datetime] = mapped_column(
        DateTime,
        nullable=False,
        server_default=func.current_timestamp(),
    )

    expiresat: Mapped[datetime] = mapped_column(
        DateTime,
        nullable=False,
    )

    completed_at: Mapped[datetime | None] = mapped_column(
        DateTime,
        nullable=True,
    )

    webhook_events: Mapped[list["WebhookEvent"]] = relationship(
        "WebhookEvent",
        back_populates="payment_session",
        cascade="all, delete-orphan",
    )

    __table_args__ = (
        Index("idx_payment_sessions_session_token", "session_token"),
        Index("idx_payment_sessions_user_email", "user_email"),
    )


class WebhookEvent(Base):
    __tablename__ = "webhook_events"

    id: Mapped[int] = mapped_column(
        Integer,
        primary_key=True,
        autoincrement=True,
    )

    session_token: Mapped[str] = mapped_column(
        String(64),
        ForeignKey("payment_sessions.session_token", ondelete="CASCADE", onupdate="CASCADE"),
        nullable=False,
    )

    event_type: Mapped[str] = mapped_column(
        String(50),
        nullable=False,
    )

    payload: Mapped[dict] = mapped_column(
        JSON,
        nullable=False,
    )

    signature: Mapped[str] = mapped_column(
        String(128),
        nullable=False,
    )

    delivered_at: Mapped[datetime] = mapped_column(
        DateTime,
        nullable=False,
        server_default=func.current_timestamp(),
    )

    payment_session: Mapped["PaymentSession"] = relationship(
        "PaymentSession",
        back_populates="webhook_events",
        foreign_keys=[session_token],
    )

    __table_args__ = (
        Index("idx_webhook_events_session_token", "session_token"),
        Index("idx_webhook_events_event_type", "event_type"),
    )


class Escrow(Base):
    __tablename__ = "escrow"

    escrow_id: Mapped[bytes] = mapped_column(
        BINARY(16),
        primary_key=True,
        server_default=func.uuid_to_bin(func.uuid()),
    )

    uid: Mapped[bytes] = mapped_column(
        BINARY(16),
        ForeignKey("users.uid", ondelete="CASCADE", onupdate="CASCADE"),
        nullable=False,
    )

    amount: Mapped[float] = mapped_column(
        Numeric(12, 2),
        nullable=False,
        server_default="0.00",
    )

    status: Mapped[str] = mapped_column(
        String(20),
        nullable=False,
        server_default="held",
        comment="held, released, refunded",
    )

    escrow_date: Mapped[datetime] = mapped_column(
        DateTime,
        nullable=False,
        server_default=func.current_timestamp(),
    )

    paid_out_date: Mapped[datetime | None] = mapped_column(
        DateTime,
        nullable=True,
    )

    user: Mapped["User"] = relationship(
        "User",
        back_populates="escrows",
        foreign_keys=[uid],
    )

    __table_args__ = (
        Index("idx_escrow_uid", "uid"),
        Index("idx_escrow_status", "status"),
    )
