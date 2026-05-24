# Classes, Responsibilities, and Collaboration (CRC) Cards

This document outlines the CRC (Class-Responsibility-Collaboration) cards for the PHP frontend services located in the `frontend/lib/services/` directory. These services form the core business logic layer for the Retrade v2 frontend platform.

---

### **1. ApiService**
**Responsibilities:**
- Facilitates HTTP communication with external backend APIs (e.g., Go microservices).
- Handles user identity validation (ID matching/image validation).
- Manages listings requests (CRUD operations for listings, search, fetching recommendations).
- Records user views for listings.
- Triggers notifications via external graph APIs (escrow notifications, payout notifications, fraud reports).

**Collaborators:**
- *None (Acts as a foundational service utilizing native `CURLFile` and HTTP requests).*

---

### **2. Auth_flow**
**Responsibilities:**
- Orchestrates the multi-step user registration and authentication flows.
- Handles forwarding and triggering the SMS OTP verification.
- Acts as a high-level facade for completing logins and finishing registration workflows.

**Collaborators:**
- `Authentication_service`
- `sms_services`

---

### **3. Authentication_service**
**Responsibilities:**
- Handles raw user persistence and domain logic for registration and logins.
- Validates passwords and checks for existing emails.
- Updates core user fields (email, names, phone number, profile images).
- Modifies user states (verifying phone, setting ID status to pending).

**Collaborators:**
- `Database` (DB abstraction layer)

---

### **4. Chat_services**
**Responsibilities:**
- Manages real-time chat rooms between buyers and sellers.
- Allows sending and retrieving messages, as well as handling chat attachments.
- Enables querying of user-specific rooms and other participants.

**Collaborators:**
- `Database`
- `profile_services`

---

### **5. delivery_service**
**Responsibilities:**
- Orchestrates the completion and delivery verification of goods.
- Interacts with escrow systems to handle payments linked with delivered items.
- Manages rate limits (e.g., PIN guessing limits for escrows) using caching.
- Triggers fund release from escrow and notifies relevant parties.

**Collaborators:**
- `ApiService`
- `Database`
- `Redis` (Cache)

---

### **6. email_service**
**Responsibilities:**
- Dispatches transactional emails.
- Sends OTPs for user verification.

**Collaborators:**
- `Dotenv` (For environment configurations)

---

### **7. listing_service**
**Responsibilities:**
- Acts as a wrapper for managing marketplace listings logic.
- Creates, updates, and retrieves listings.
- Intercepts requests to handle side effects like "viewing" a listing and automatically opening/fetching a chat room with the seller when needed.

**Collaborators:**
- `ApiService`
- `Chat_services`

---

### **8. order_service**
**Responsibilities:**
- Manages the creation of orders originating from distinct business contexts (e.g., shops vs. marketplace listings).
- Handles database transactions for order creation.

**Collaborators:**
- `Database`
- `profile_services`

---

### **9. PaymentGatewaysServices**
**Responsibilities:**
- Serves as the facade for interacting with external and fake internal payment processors.
- Generates payment sessions and tracking states.
- Reconciles webhook events and logs fake bank test payments.
- Directly routes payments and updates session status upon successful transactions.

**Collaborators:**
- `ApiService`
- `Database`
- `supabase_service`

---

### **10. profile_services**
**Responsibilities:**
- Provides a comprehensive view of a user's profile and verification states.
- Checks verification booleans (Email, Phone, ID).
- Handles actions for updating profile information.
- Sends and validates email verification OTPs.

**Collaborators:**
- `Database`
- `Authentication_service`
- `email_service`
- `sms_services`

---

### **11. Report_service**
**Responsibilities:**
- Captures and records users reporting on other users or filing disputes.
- Relays fraud reports to external graph services.
- Maintains a history of user disputes correlated with orders or payments.

**Collaborators:**
- `ApiService`
- `Database`

---

### **12. shop_service**
**Responsibilities:**
- Manages mass C2C entities (Shops/Stores).
- Handles CRUD actions for shop profiles and individual shop products.
- Manages cart functionality specific to shops (creating carts, adding/removing items, updating quantities).

**Collaborators:**
- `Database`
- `profile_services`

---

### **13. sms_services**
**Responsibilities:**
- Handles dispatching SMS messages using external SMS providers.
- Normalizes phone numbers for integration formatting.
- Sends OTP codes to phone numbers.

**Collaborators:**
- `Dotenv`

---

### **14. supabase_service**
**Responsibilities:**
- Standardizes interaction with Supabase API for BaaS functionality.
- Facilitates the "Fake Bank" functions (testing balances, simulated charging/deductions).

**Collaborators:**
- *Supabase API abstraction*

---

### **15. System Upload Tool (`upload.php`)**
- *Note: Mostly procedural and not an OOP class, but a core system dependency.*
**Responsibilities:**
- Evaluates file types through streams and manages cloud uploads using the AWS SDK (S3).
**Collaborators:** 
- `Aws\S3\S3Client`
