from sqlalchemy import (
	BINARY, ForeignKey, DateTime, Text, String, func, CheckConstraint, UniqueConstraint, Index, text
)
from sqlalchemy.orm import Mapped, mapped_column, relationship
from app.db.base import Base


class ChatRoom(Base):
	__tablename__ = "chat_rooms"

	room_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		primary_key=True,
		server_default=func.uuid_to_bin(func.uuid())
	)

	created_at: Mapped[str] = mapped_column(
		DateTime,
		nullable=False,
		server_default=func.current_timestamp()
	)

	user_one: Mapped[bytes] = mapped_column(
		BINARY(16),
		ForeignKey("users.uid", ondelete="CASCADE", onupdate="CASCADE"),
		nullable=False
	)

	user_two: Mapped[bytes] = mapped_column(
		BINARY(16),
		ForeignKey("users.uid", ondelete="CASCADE", onupdate="CASCADE"),
		nullable=False
	)

	# Relationships
	# Note: These relationships require careful handling in query logic 
	# as a room belongs to two users.
	messages: Mapped[list["ChatMessage"]] = relationship(
		"ChatMessage",
		back_populates="room",
		cascade="all, delete-orphan"
	)

	__table_args__ = (
		Index(
			"uq_room_pair",
			text("LEAST(user_one, user_two)"),
			text("GREATEST(user_one, user_two)"),
			unique=True
		),
		Index("idx_rooms_user_one", "user_one"),
		Index("idx_rooms_user_two", "user_two"),
	)


class ChatMessage(Base):
	__tablename__ = "chat_messages"

	message_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		primary_key=True,
		server_default=func.uuid_to_bin(func.uuid())
	)

	room_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		ForeignKey("chat_rooms.room_id", ondelete="CASCADE", onupdate="CASCADE"),
		nullable=False
	)

	sender_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		ForeignKey("users.uid", ondelete="CASCADE", onupdate="CASCADE"),
		nullable=False
	)

	message_text: Mapped[str | None] = mapped_column(
		Text,
		nullable=True
	)

	attachment_id: Mapped[bytes | None] = mapped_column(
		BINARY(16),
		nullable=True
	)

	sent_at: Mapped[str] = mapped_column(
		DateTime,
		nullable=False,
		server_default=func.current_timestamp()
	)

	# Relationships
	room: Mapped["ChatRoom"] = relationship(
		"ChatRoom",
		back_populates="messages"
	)

	sender: Mapped["User"] = relationship(
		"User",
		foreign_keys=[sender_id]
	)

	attachment: Mapped["ChatAttachment"] = relationship(
		"ChatAttachment",
		back_populates="message",
		uselist=False
	)

	__table_args__ = (
		Index("idx_messages_room", "room_id"),
		Index("idx_messages_sent_at", "sent_at"),
	)


class ChatAttachment(Base):
	__tablename__ = "chat_attachments"

	attachment_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		primary_key=True,
		server_default=func.uuid_to_bin(func.uuid())
	)

	message_id: Mapped[bytes] = mapped_column(
		BINARY(16),
		ForeignKey("chat_messages.message_id", ondelete="CASCADE", onupdate="CASCADE"),
		nullable=False
	)

	attachment_url: Mapped[str] = mapped_column(
		Text,
		nullable=False,
		comment="Stored in MinIO or similar"
	)

	file_type: Mapped[str] = mapped_column(
		String(20),
		nullable=False
	)

	created_at: Mapped[str] = mapped_column(
		DateTime,
		nullable=False,
		server_default=func.current_timestamp()
	)

	# Relationships
	message: Mapped["ChatMessage"] = relationship(
		"ChatMessage",
		back_populates="attachment"
	)

	__table_args__ = (
		Index("idx_attachments_message", "message_id"),
	)