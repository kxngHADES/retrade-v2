CREATE TABLE IF NOT EXISTS cart_items (
	item_id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
	cart_id BINARY(16) NOT NULL,
	shop_id BINARY(16) NOT NULL,
	product_id BINARY(16) NOT NULL,
	quantity INTEGER NOT NULL DEFAULT 1,
	price_snapshot DECIMAL(10, 2) NOT NULL DEFAULT 0.00,

	CONSTRAINT pk_cart_items PRIMARY KEY (item_id),

	CONSTRAINT fk_items_cart FOREIGN KEY (cart_id)
		REFERENCES carts(cart_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,

	CONSTRAINT fk_items_shop FOREIGN KEY (shop_id)
		REFERENCES shops(shop_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,

	CONSTRAINT fk_items_product FOREIGN KEY (product_id)
		REFERENCES shop_products(product_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,

	CONSTRAINT uq_cart_product UNIQUE (cart_id, product_id),

	INDEX idx_items_cart (cart_id),
	INDEX idx_items_product (product_id),
	INDEX idx_items_shop (shop_id)
) ENGINE=InnoDB;