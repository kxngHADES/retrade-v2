CREATE TABLE IF NOT EXISTS payment_sessions (
    paymentSession_id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending','processing','success','failed','expired') DEFAULT 'pending',
    webhook_delivered TINYINT(1) DEFAULT 0,
    webhook_attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiresat TIMESTAMP NOT NULL,
    completed_at TIMESTAMP NULL
) ENGINE=InnoDB;