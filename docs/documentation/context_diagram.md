# Context Diagram (Level 0 DFD)

The Context Diagram defines the boundary of the **Retrade v2 System**, illustrating how it interacts with external entities such as users, administrators, and third-party services (payment gateways, notification providers, and cloud storage).

## System Context Visualization

```mermaid
flowchart TD
    %% External Entities
    User((User<br/>Buyer/Seller))
    Admin((Administrator))
    PaymentAPI(((Payment Gateway<br/>Stripe / FakeBank)))
    SMSAPI(((SMS Provider)))
    EmailAPI(((Email Provider)))
    Storage(((Cloud Storage<br/>S3 / MinIO)))
    GraphEngine(((Graph / Recommendation<br/>Engine)))

    %% The System
    System[/"<b>Retrade v2 System</b><br/>(Marketplace, Shops, Chat, Escrow)"/]

    %% Interactions
    User -- "Browses products, registers, sends messages, <br/>places orders, pays, files reports" --> System
    System -- "Order status, chat messages, <br/>product recommendations, OTPs" --> User
    
    Admin -- "Reviews disputes, manages users, <br/>oversees platform health" --> System
    System -- "Flagged accounts, escalated disputes, <br/>system metrics" --> Admin

    System -- "Payment intents, refund requests" --> PaymentAPI
    PaymentAPI -- "Webhooks, payment confirmations" --> System

    System -- "Sends SMS OTPs & alerts" --> SMSAPI
    System -- "Sends email notifications & verifications" --> EmailAPI

    System -- "Uploads product images, <br/>chat attachments, ID verifications" --> Storage
    Storage -- "Serves media files" --> System

    System -- "User viewing metrics, <br/>cross-linked fraud data" --> GraphEngine
    GraphEngine -- "Personalized feeds, <br/>fraud alerts" --> System

    classDef system fill:#2b78e4,stroke:#333,stroke-width:2px,color:#fff;
    classDef entity fill:#f9f9f9,stroke:#333,stroke-width:2px;
    classDef service fill:#e8f4f8,stroke:#2b78e4,stroke-width:2px;

    class System system;
    class User,Admin entity;
    class PaymentAPI,SMSAPI,EmailAPI,Storage,GraphEngine service;
```

## External Entities Overview

1. **User (Buyer/Seller)**: The primary actor interacting with the platform. They can explore marketplace items or dedicated shops, communicate, initiate escrow transactions, and flag issues.
2. **Administrator**: Platform moderators responsible for observing and resolving disputes, verifying identities, and actioning fraud flags.
3. **Payment Gateway**: Third-party APIs processing fiat and simulated ("Fake Bank") transactions, receiving payment instructions and emitting webhooks.
4. **Cloud Storage (S3/MinIO)**: Remote object storage that retains heavy media items like listing pictures, chat attachments, and submitted ID verifications.
5. **Graph / Recommendation Engine**: External Go-based microservices mapping relationships for personalized feeds and capturing fraud rings.
6. **SMS & Email Providers**: Standard outgoing communication endpoints delivering Multi-Factor Authentication (OTP) codes and order receipts.
