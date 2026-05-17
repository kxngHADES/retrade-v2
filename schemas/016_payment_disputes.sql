CREATE TABLE IF NOT EXISTS payment_disputes (
	dispute_id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
	reporter_id BINARY(16) NOT NULL COMMENT 'The user (buyer) who is disputing the transaction',
	order_id BINARY(16) NOT NULL COMMENT 'The order that is being disputed',
	payment_reference VARCHAR(255) NULL COMMENT 'Reference to the specific payment, escrow, or session ID',
	dispute_reason VARCHAR(255) NOT NULL COMMENT 'e.g., ''falsified_delivery'', ''item_not_received'', ''fraudulent_activity''',
	description TEXT NOT NULL COMMENT 'Detailed explanation of how they were tricked',
	evidence_urls JSON NULL COMMENT 'JSON array of image or document URLs proving the claim',
	status VARCHAR(50) NOT NULL DEFAULT 'open' COMMENT 'open, investigating, payment_reversed, rejected',
	admin_resolution_notes TEXT NULL COMMENT 'Explanation from the admin upon resolving/reversing the payment',
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

	CONSTRAINT pk_payment_disputes PRIMARY KEY (dispute_id),
	CONSTRAINT fk_disputes_reporter FOREIGN KEY (reporter_id) REFERENCES users(uid) ON DELETE CASCADE
	-- Assuming your orders table primary key is also BINARY(16). If it references `orders(order_id)`, you can uncomment the next line:
	-- , CONSTRAINT fk_disputes_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
) ENGINE=InnoDB;
