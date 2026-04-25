CREATE TABLE IF NOT EXISTS chat_messages (
    message_id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
    room_id BINARY(16) NOT NULL,
    sender_id BINARY(16) NOT NULL,
    message_text TEXT NULL,
    attachment_id BINARY(16) NULL,
    sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_message PRIMARY KEY (message_id),

    CONSTRAINT fk_message_room FOREIGN KEY (room_id)
        REFERENCES chat_rooms(room_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_message_sender FOREIGN KEY (sender_id)
        REFERENCES users(uid)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;