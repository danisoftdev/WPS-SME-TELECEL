## WPS-SME API (v1)

This API lets **external websites/apps** login and place Telecel bundle orders on this platform.  
Delivery is **manual** (Supplier/Admin updates order status in the dashboard).

### Base URL

- **Local**: `http://127.0.0.1:8000/api`
- **Production**: `https://your-domain.com/api`

### Authentication

This API uses **Laravel Sanctum Personal Access Tokens**.

#### 1) Login (get token)

`POST /login`

Body (JSON):

```json
{
  "email": "user@example.com",
  "password": "your-password"
}
```

Response:

```json
{
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "token_type": "Bearer",
  "abilities": [
    "account:read",
    "wallet:read",
    "wallet:transactions:read",
    "wallet:topup",
    "orders:read",
    "orders:write",
    "plans:read",
    "plans:subscriptions:read",
    "stores:read",
    "stores:write",
    "payout:read",
    "payout:write",
    "withdrawals:read",
    "withdrawals:write"
  ],
  "user": {
    "id": 2,
    "name": "John Doe",
    "email": "user@example.com",
    "roles": ["Wholesaler"]
  }
}
```

`abilities` depends on the account (e.g. `orders:write` only if the user can place orders). **Re-login after upgrades** so the token includes new abilities.

Use the token in all calls:

- Header: `Authorization: Bearer <token>`
- Header: `Accept: application/json`

#### 2) Logout (revoke current token)

`POST /v1/logout`

### Core endpoints

#### Current user

`GET /v1/me`

Response:

```json
{
  "id": 2,
  "name": "John Doe",
  "email": "user@example.com",
  "roles": ["Wholesaler"],
  "wallet_balance": 488,
  "is_frozen": false,
  "momo_phone": null,
  "momo_account_name": null
}
```

#### Wallet

- `GET /v1/wallet`
- `GET /v1/wallet/transactions?limit=50` — each item includes `reference_type` and `reference_id` (e.g. `shop_sale`, `paystack_topup`, `withdrawal_request`).
- `POST /v1/wallet/topups` — start a Paystack top-up; response includes `pay_url` (open in browser / WebView) and `reference`.
- `GET /v1/wallet/topups/{reference}` — status of a top-up you created (`pending`, `success`, `failed`).

##### Start wallet top-up

`POST /v1/wallet/topups`

```json
{ "amount": 50 }
```

Response (201):

```json
{
  "message": "Top-up initiated. Open pay_url to complete payment.",
  "reference": "WPS-XXXXXXXXXXXX-1234567890",
  "amount": 50,
  "currency": "GHS",
  "pay_url": "https://your-domain.com/wallet/topups/WPS-..."
}
```

Paystack keys are the ones configured in **Admin → System settings → Paystack** (same as the web app).

#### Plans & subscriptions (for the logged-in reseller)

- `GET /v1/plans`
- `GET /v1/subscriptions`

#### Public shop (no auth token)

For mobile apps or partner sites listing a merchant’s catalog and starting guest checkout. Throttled per IP.

- `GET /v1/shops/{slug}/catalog` — store profile + priced products (`bundle_subscription_id`, `price`, etc.).
- `POST /v1/shops/{slug}/checkout` — body: `bundle_subscription_id`, `customer_phone`. Returns `pay_url` for Paystack (same flow as the public web shop).

#### Stores (your own)

- `GET /v1/stores?limit=50`
- `POST /v1/stores`
- `GET /v1/stores/{slug}` — `{slug}` is the store slug (same as in `/shop/{slug}`).
- `PUT /v1/stores/{slug}`
- `DELETE /v1/stores/{slug}` — fails with 422 if the store still has sub-agents.

Create/update body (JSON) matches the web form: `name`, `whatsapp_phone` (required), optional `description`, `contact_email`, `location`, optional boolean `is_active`.

##### Catalog prices (owner)

- `GET /v1/stores/{slug}/pricing` — list active bundles with `base_amount` and your `selling_price` (null if not set).
- `PUT /v1/stores/{slug}/pricing` — body: `{ "prices": { "1": "10.50", "2": "" } }` — empty string removes a price; each set price must be **strictly greater** than the platform base (same rule as the web).

#### Payout profile (MoMo)

- `GET /v1/payout`
- `PUT /v1/payout` — body: `momo_phone`, `momo_account_name` (both required when updating).

#### Withdrawals

- `GET /v1/withdrawals?limit=50`
- `POST /v1/withdrawals` — body: `{ "amount": 100 }` — requires completed payout profile and available balance; one pending request at a time.

#### Orders

- `GET /v1/orders?status=PENDING&from_date=2026-03-01&to_date=2026-03-31&limit=50`
- `GET /v1/orders/{id}`
- `POST /v1/orders`

##### Place order

`POST /v1/orders`

Body (JSON):

```json
{
  "phone_number": "0501234567",
  "reseller_plan_id": 10,
  "confirmation_checked": true,
  "network": "Telecel"
}
```

Success response (201):

```json
{
  "message": "Order placed.",
  "order": {
    "id": 123,
    "phone_number": "0501234567",
    "amount": 4,
    "status": "PENDING",
    "created_at": "2026-03-18T10:11:12+00:00",
    "plan": { "data_size_gb": 1, "price": 4 },
    "network": "Telecel"
  }
}
```

### Copy/paste examples (cURL)

#### Login

```bash
curl -X POST "http://127.0.0.1:8000/api/login" ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"email\":\"admin@wps-sme.local\",\"password\":\"password\"}"
```

#### Get my wallet

```bash
curl "http://127.0.0.1:8000/api/v1/wallet" ^
  -H "Accept: application/json" ^
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Place an order

```bash
curl -X POST "http://127.0.0.1:8000/api/v1/orders" ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -H "Authorization: Bearer YOUR_TOKEN_HERE" ^
  -d "{\"phone_number\":\"0501234567\",\"reseller_plan_id\":10,\"confirmation_checked\":true,\"network\":\"Telecel\"}"
```

#### List orders

```bash
curl "http://127.0.0.1:8000/api/v1/orders?limit=20" ^
  -H "Accept: application/json" ^
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Notes for embedding / external sites

- The external website should **never** expose the token in front-end JS.
- Best practice: external site creates a small backend (PHP/Node/etc.) that:
  - logs in (or stores a token),
  - calls this API server-to-server,
  - returns only necessary data to its frontend.

