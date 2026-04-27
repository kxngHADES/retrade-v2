CREATE TABLE IF NOT EXISTS payment (
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
) ENGINE=InnoDB;