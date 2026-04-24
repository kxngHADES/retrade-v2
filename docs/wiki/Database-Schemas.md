# Database Schemas

This page documents the current SQL schemas in `/schemas`.

## Tables

### users (`001_users.sql`)

- Primary key: `uid`
- Unique keys: `email`, `phoneNumber`
- Stores profile, verification, role, ban state, location, and timestamps

### shops (`002_shops.sql`)

- Primary key: `shop_id`
- Foreign key: `uid -> users(uid)`
- Unique keys: `uid`, `shop_name`

### shop_products (`003_shop_product.sql`)

- Primary key: `product_id`
- Foreign key: `shop_id -> shops(shop_id)`
- Stores product metadata, stock, price, and active status

### carts (`004_carts.sql`)

- Primary key: `cart_id`
- Foreign keys:
  - `uid -> users(uid)`
  - `shop_id -> shops(shop_id)`
- Uses generated `active_cart_key` to enforce one active cart per user+shop pair

### cart_items (`004_cart_items.sql`)

- Primary key: `item_id`
- Foreign keys:
  - `cart_id -> carts(cart_id)`
  - `shop_id -> shops(shop_id)`
  - `product_id -> shop_products(product_id)`
- Unique key: `(cart_id, product_id)` to prevent duplicate line items

## Relationship Flow

`users -> shops -> shop_products` and `users + shops -> carts -> cart_items`
