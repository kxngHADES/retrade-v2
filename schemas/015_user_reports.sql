CREATE TABLE IF NOT EXISTS user_reports (
	report_id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
	reporter_id BINARY(16) NOT NULL COMMENT 'The user who is filing the report',
	report_type VARCHAR(50) NOT NULL COMMENT 'Type of report: e.g., ''user'', ''delivery'', ''shop'', ''product'', ''other''',
	target_reference_id VARCHAR(255) NULL COMMENT 'ID of the reported entity (e.g., user_id, order_id, product_id)',
	reason VARCHAR(255) NOT NULL,
	description TEXT NULL,
	status VARCHAR(50) NOT NULL DEFAULT 'pending' COMMENT 'pending, investigating, resolved, dismissed',
	admin_notes TEXT NULL COMMENT 'Internal notes for admins handling the report',
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

	CONSTRAINT pk_user_reports PRIMARY KEY (report_id),
	CONSTRAINT fk_user_reports_reporter FOREIGN KEY (reporter_id) REFERENCES users(uid) ON DELETE CASCADE
) ENGINE=InnoDB;
