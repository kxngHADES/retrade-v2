CREATE TABLE IF NOT EXISTS orders (
    order_id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
    buyer_uid BINARY(16) NOT NULL,
    seller_uid BINARY(16) NOT NULL,
    shop_id BINARY(16) DEFAULT NULL,
    cart_id BINARY(16) DEFAULT NULL,
    listing_id VARCHAR(64) DEFAULT NULL COMMENT 'MongoDB ObjectId for individual marketplace listings',
    order_type VARCHAR(32) NOT NULL COMMENT 'marketplace or shop',
    total_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    status TINYINT(3) NOT NULL DEFAULT 0 COMMENT '0=pending, 1=paid, 2=shipped, 3=completed, 4=cancelled',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_orders PRIMARY KEY (order_id),

    CONSTRAINT fk_orders_buyer FOREIGN KEY (buyer_uid)
        REFERENCES users(uid)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_orders_seller FOREIGN KEY (seller_uid)
        REFERENCES users(uid)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_orders_shop FOREIGN KEY (shop_id)
        REFERENCES shops(shop_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_orders_cart FOREIGN KEY (cart_id)
        REFERENCES carts(cart_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    INDEX idx_orders_buyer (buyer_uid),
    INDEX idx_orders_seller (seller_uid),
    INDEX idx_orders_shop (shop_id),
    INDEX idx_orders_cart (cart_id),
    INDEX idx_orders_listing (listing_id)
) ENGINE=InnoDB;