CREATE TABLE IF NOT EXISTS chat_attachments (
    attachment_id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
    message_id BINARY(16) NOT NULL,
    attachment_url TEXT NOT NULL COMMENT 'Stored in MinIO or similar',
    file_type VARCHAR(20) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_attachment PRIMARY KEY (attachment_id),

    CONSTRAINT fk_attachment_message FOREIGN KEY (message_id)
        REFERENCES chat_messages(message_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;