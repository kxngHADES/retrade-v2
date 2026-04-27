CREATE TABLE IF NOT EXISTS webhook_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paymentSession_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL,  -- 'payment.success' | 'payment.failed'
    payload JSON NOT NULL,
    signature VARCHAR(128) NOT NULL,
    delivered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_webhook_ps FOREIGN KEY (paymentSession_id) REFERENCES payment_sessions(paymentSession_id) ON DELETE CASCADE
) ENGINE=InnoDB;