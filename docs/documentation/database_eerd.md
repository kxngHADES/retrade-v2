# Enhanced Entity Relationship Diagram (EERD)

This document contains the Enhanced Entity Relationship Diagram (EERD) mapping the database architecture for Retrade v2. The mapping relies on the primary MySQL schema declarations corresponding to the system logic, as well as the backend Pydantic models.

## Schema Overview

The system includes multiple modules working cohesively:
1. **Users & Authentication:** `users`, `admins`
2. **E-Commerce / C2C (Shops):** `shops`, `shop_products`, `carts`, `cart_items`
3. **Peer-to-Peer & Communication:** `chat_rooms`, `chat_messages`, `chat_attachments`
4. **Orders & Transactions:** `orders`, `payment`, `bank`, `escrow`, `payment_sessions`, `webhook_events`
5. **Moderation:** `user_reports`, `payment_disputes`

---

## EERD Visualization

```mermaid
erDiagram
    %% Core User & Admin
    USERS {
        binary(16) uid PK
        string firstName
        string lastName
        string email UK
        string phoneNumber UK
        boolean is_phone_verified
        boolean is_email_verified
        int is_id_verified
        string profile_image_url
        int rbac_role
        boolean is_banned
        datetime ban_expires_at
        datetime created_at
        datetime updated_at
        point location_point
    }
    
    ADMINS {
        binary(16) admin_id PK
        string username UK
        string email UK
        string password
        int role "1=admin, 2=superadmin"
        datetime created_at
        datetime updated_at
    }

    %% B2C Shops Module
    SHOPS {
        binary(16) shop_id PK
        binary(16) uid FK "Creator"
        string shop_name UK
        boolean status
    }

    SHOP_PRODUCTS {
        binary(16) product_id PK
        binary(16) shop_id FK
        string name
        text description
        int stock_quantity
        decimal price
        boolean is_active
    }

    CARTS {
        binary(16) cart_id PK
        binary(16) uid FK
        binary(16) shop_id FK
        boolean status
        varbinary active_cart_key UK
        datetime updated_at
    }

    CART_ITEMS {
        binary(16) item_id PK
        binary(16) cart_id FK
        binary(16) shop_id FK
        binary(16) product_id FK
        int quantity
        decimal price_snapshot
    }

    %% Communication Module
    CHAT_ROOMS {
        binary(16) room_id PK
        datetime created_at
        binary(16) user_one FK
        binary(16) user_two FK
    }

    CHAT_MESSAGES {
        binary(16) message_id PK
        binary(16) room_id FK
        binary(16) sender_id FK
        text message_text
        binary(16) attachment_id
        datetime sent_at
    }

    CHAT_ATTACHMENTS {
        binary(16) attachment_id PK
        binary(16) message_id FK
        text attachment_url
        string file_type
        datetime created_at
    }

    %% Transactions & Orders Module
    ORDERS {
        binary(16) order_id PK
        binary(16) buyer_uid FK
        binary(16) seller_uid FK
        binary(16) shop_id FK "Nullable"
        binary(16) cart_id FK "Nullable"
        string listing_id "Nullable, MongoDB Ref"
        string order_type "marketplace | shop"
        decimal total_amount
        int status "0=pending, 1=paid, 2=shipped, 3=completed, 4=cancelled"
        datetime created_at
        datetime updated_at
    }

    PAYMENT {
        binary(16) payment_id PK
        binary(16) order_id FK
        string payment_gateway_reference UK
        decimal amount
        string currency
        int payment_status "0=pending, 1=success, 2=failed, 3=refunded"
        datetime created_at
        datetime updated_at
    }

    BANK {
        binary(16) transaction_id PK
        binary(16) uid FK
        decimal amount
        string transaction_type "deposit | withdrawal | payment | receipt"
        string status "pending | completed | failed"
        datetime created_at
    }

    ESCROW {
        binary(16) escrow_id PK
        binary(16) payment_id FK
        binary(16) uid FK "Seller awaiting funds"
        decimal amount
        string pin_code "Encrypted"
        string status "held | released | refunded | disputed"
        datetime created_at
        datetime release_at "Nullable"
    }

    PAYMENT_SESSIONS {
        int id PK "Auto Increment"
        string email
        decimal amount
        json metadata
        string status "pending | locked | completed | failed"
        datetime created_at
        datetime updated_at
        datetime expires_at
    }

    WEBHOOK_EVENTS {
        int id PK "Auto Increment"
        int paymentSession_id FK
        string event_type
        json payload
        string status "received | processed | failed"
        datetime created_at
    }

    %% Moderation Module
    USER_REPORTS {
        binary(16) report_id PK
        binary(16) reporter_id FK
        string report_type
        string target_reference_id
        string reason
        text description
        string status "pending | investigating | resolved | dismissed"
        datetime created_at
        datetime updated_at
    }

    PAYMENT_DISPUTES {
        binary(16) dispute_id PK
        binary(16) reporter_id FK
        binary(16) order_id FK "Logical Link"
        string payment_reference
        string dispute_reason
        text description
        json evidence_urls
        string status
        datetime created_at
        datetime updated_at
    }

    %% Relationships
    USERS ||--o{ SHOPS : "owns"
    USERS ||--o{ CARTS : "has"
    USERS ||--o{ CHAT_ROOMS : "participates in (user_one)"
    USERS ||--o{ CHAT_ROOMS : "participates in (user_two)"
    USERS ||--o{ CHAT_MESSAGES : "sends"
    USERS ||--o{ ORDERS : "buys as buyer_uid"
    USERS ||--o{ ORDERS : "sells as seller_uid"
    USERS ||--o{ BANK : "performs bank transactions"
    USERS ||--o{ ESCROW : "is party to"
    USERS ||--o{ USER_REPORTS : "files report"
    USERS ||--o{ PAYMENT_DISPUTES : "files dispute"

    SHOPS ||--o{ SHOP_PRODUCTS : "lists"
    SHOPS ||--o{ CARTS : "has carts"
    SHOPS ||--o{ CART_ITEMS : "has items"
    SHOPS ||--o{ ORDERS : "fulfills"

    SHOP_PRODUCTS ||--o{ CART_ITEMS : "added to"

    CARTS ||--o{ CART_ITEMS : "contains"
    CARTS ||--o| ORDERS : "converted to"

    CHAT_ROOMS ||--o{ CHAT_MESSAGES : "contains"
    
    CHAT_MESSAGES ||--o| CHAT_ATTACHMENTS : "has"

    ORDERS ||--o| PAYMENT : "paid via"
    ORDERS ||--o{ PAYMENT_DISPUTES : "disputed in"

    PAYMENT ||--o| ESCROW : "held in"

    PAYMENT_SESSIONS ||--o{ WEBHOOK_EVENTS : "receives"
```

## Relationship Details

### Core Business Domains

1. **User Identity & Context**
   - The `USERS` table serves as the primary anchor point. Practically every other context (Shops, Carts, Chat, Orders, Payments, moderation) leverages `users.uid` as a Foreign Key. This establishes a fully traceable lineage for user transactions and platform interactions.
   
2. **Shop Cart Mechanics**
   - The combination of a Cart (`CARTS`) linking back to a Shop ensures B2B/B2C logic separation from marketplace orders. A cart contains `CART_ITEMS`, which directly reference `SHOP_PRODUCTS`. Upon checkout, a single `CART_ID` can seamlessly spawn an `ORDER_ID`.

3. **Inter-User Communications**
   - The `CHAT_ROOMS` act as a bridge explicitly joining two `users(uid)` elements (`user_one` and `user_two`). Then, `CHAT_MESSAGES` tracks individual messages and dynamically attaches multimedia files via `CHAT_ATTACHMENTS`.

4. **Orders, Escrow & Payment Gateway**
   - A critical link is `ORDERS` branching to `PAYMENT`. Due to the nature of a peer-to-peer and commerce marketplace, every Payment that isn't instantly completed lands in the `ESCROW` table, mapped by `payment_id`, locking funds until released.
   - Pydantic models (like `PaymentDisputeCreate` and `EscrowReleaseRequest`) strongly enforce the validation bridging these tables dynamically.

5. **Moderation Traceability**
   - `PAYMENT_DISPUTES` are deeply connected to the transactional layer by tracing both `reporter_id` (Buyer `uid`) and the relevant `order_id`. Admins triage these logs against `USER_REPORTS` to ban or freeze problematic `users(uid)`.
