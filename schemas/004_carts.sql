CREATE TABLE IF NOT EXISTS carts (
	cart_id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
	uid BINARY(16) NOT NULL,
	shop_id BINARY(16) NOT NULL,
	status TINYINT(1) NOT NULL DEFAULT 1,
	
	active_cart_key VARBINARY(32) 
		GENERATED ALWAYS AS (
			CASE 
				WHEN status = 1 THEN CONCAT(uid, shop_id)
				ELSE NULL
			END
		) STORED,

	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP 
		ON UPDATE CURRENT_TIMESTAMP,

	CONSTRAINT pk_cart PRIMARY KEY (cart_id),

	CONSTRAINT fk_carts_uid FOREIGN KEY (uid)
		REFERENCES users(uid)
		ON DELETE CASCADE
		ON UPDATE CASCADE,

	CONSTRAINT fk_carts_shop FOREIGN KEY (shop_id)
		REFERENCES shops(shop_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,

	UNIQUE KEY unique_active_cart (active_cart_key),

	INDEX idx_carts_uid (uid),
	INDEX idx_carts_shop (shop_id)
) ENGINE=InnoDB;