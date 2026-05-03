CREATE TABLE IF NOT EXISTS bank (
    uid BINARY(16) NOT NULL,
    card_number_hash VARCHAR(255) NOT NULL COMMENT 'PHP default password_hash() output for the card number',
    exp_date CHAR(7) NOT NULL COMMENT 'MM/YYYY',
    cvv_hash VARCHAR(255) NOT NULL COMMENT 'PHP default password_hash() output for the CVV',
    cardholder_name VARCHAR(255) NOT NULL,
    billing_address VARCHAR(255) NULL,

    CONSTRAINT fk_bank_user FOREIGN KEY (uid)
        REFERENCES users(uid)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;CREATE TABLE IF NOT EXISTS payment (
    payment_id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
    order_id BINARY(16) NOT NULL,
    status TINYINT(3) NOT NULL DEFAULT 0 COMMENT '0=pending, 1=success, 2=failed',
    amount DECIMAL(12, 2) NOT NULL,
    reference VARCHAR(255) NOT NULL,
    paid_at DATETIME DEFAULT NULL,
    pin CHAR(5) NOT NULL,

    CONSTRAINT pk_payment PRIMARY KEY (payment_id),
    CONSTRAINT fk_payment_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT uq_payment_reference UNIQUE (reference),
    CONSTRAINT chk_payment_pin CHECK (pin REGEXP '^[0-9]{5}$')
) ENGINE=InnoDB;CREATE TABLE IF NOT EXISTS escrow (
    escrow_id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
    uid BINARY(16) NOT NULL,
    amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    status ENUM('held','released','refunded') NOT NULL DEFAULT 'held',
    escrow_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paid_out_date DATETIME DEFAULT NULL,

    CONSTRAINT pk_escrow PRIMARY KEY (escrow_id),
    CONSTRAINT fk_escrow_user FOREIGN KEY (uid)
        REFERENCES users(uid)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX idx_escrow_user (uid),
    INDEX idx_escrow_status (status)
) ENGINE=InnoDB;CREATE TABLE IF NOT EXISTS payment_sessions (
    paymentSession_id INT AUTO_INCREMENT PRIMARY KEY,
    session_token VARCHAR(64) UNIQUE NOT NULL,
    user_email VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending','processing','success','failed','expired') DEFAULT 'pending',
    webhook_delivered TINYINT(1) DEFAULT 0,
    webhook_attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiresat TIMESTAMP NOT NULL,
    completed_at TIMESTAMP NULL,
)CREATE TABLE IF NOT EXISTS webhook_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_token VARCHAR(64) NOT NULL,
    event_type VARCHAR(50) NOT NULL,  -- 'payment.success' | 'payment.failed'
    payload JSON NOT NULL,
    signature VARCHAR(128) NOT NULL,
    delivered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)