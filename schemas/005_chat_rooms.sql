CREATE TABLE IF NOT EXISTS chat_rooms (
    room_id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_one BINARY(16) NOT NULL,
    user_two BINARY(16) NOT NULL,

    CONSTRAINT pk_room PRIMARY KEY (room_id),

    CONSTRAINT fk_user_one FOREIGN KEY (user_one) 
        REFERENCES users(uid)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_user_two FOREIGN KEY (user_two) 
        REFERENCES users(uid)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT chk_not_same_user CHECK (user_one <> user_two),

    UNIQUE KEY uq_room_pair (LEAST(user_one, user_two), GREATEST(user_one, user_two))
) ENGINE=InnoDB;