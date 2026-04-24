CREATE TABLE IF NOT EXISTS shop_products (
	product_id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
	shop_id BINARY(16) NOT NULL,
	name VARCHAR(255) NOT NULL,
	description TEXT NOT NULL,
	stock_quantity INTEGER NOT NULL,
	price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
	is_active TINYINT(1) NOT NULL,

	CONSTRAINT pk_product PRIMARY KEY (product_id),

	CONSTRAINT fk_products_shop FOREIGN KEY (shop_id)
		REFERENCES shops(shop_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,

	INDEX idx_shop_products_shop (shop_id)
) ENGINE=InnoDB;