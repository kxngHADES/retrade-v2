CREATE TABLE IF NOT EXISTS escrow (
    escrow_id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
    payment_id BINARY(16) NOT NULL,
    uid BINARY(16) NOT NULL,
    amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    status ENUM('held','released','refunded') NOT NULL DEFAULT 'held',
    escrow_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paid_out_date DATETIME DEFAULT NULL,

    CONSTRAINT pk_escrow PRIMARY KEY (escrow_id),
    CONSTRAINT fk_escrow_payment FOREIGN KEY (payment_id)
        REFERENCES payment(payment_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_escrow_user FOREIGN KEY (uid)
        REFERENCES users(uid)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX idx_escrow_payment (payment_id),
    INDEX idx_escrow_user (uid),
    INDEX idx_escrow_status (status)
) ENGINE=InnoDB;