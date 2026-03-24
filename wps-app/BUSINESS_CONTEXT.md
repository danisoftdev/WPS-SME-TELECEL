# Business context — SaaS, agents, sub-agents, and iGate

This document is the **product reference** for turning this platform into a **SaaS** offering: the operator runs the main product (catalog, base pricing, wallets, fulfillment / iGate), while **agents** and **sub-agents** operate on top of it via the web app and **API**.

## SaaS positioning

- **Platform (you):** Owns the product catalog (e.g. MTN / Telesub / Telesor-style SKUs), **base prices**, compliance rules, and the integration to **iGate** (or equivalent). You may later add subscription plans, usage limits, and billing per agent.
- **Agents (customers of the SaaS):** Pay or subscribe to use the platform; create **stores**, onboard **sub-agents**, and/or connect **their own sites** via **API** so orders flow automatically.
- **Sub-agents:** Operate under an agent’s store (unique link); configure **their** resale prices with a **mandatory margin** above base.
- **End customers:** Buy through sub-agent storefronts or agent channels; order execution still ties back to platform rules and fulfillment.

## Order flow and iGate

- **Before:** Agents who ran their own sites submitted orders to **iGate** manually. That was slow and stressful, especially for bulk orders.
- **Now:** **API integration** lets each agent’s system submit orders to iGate **automatically**. The platform operator already runs iGate and supports these integrations.
- **SaaS implication:** API keys, rate limits, and audit trails should be scoped per **agent** (tenant), with optional per-store or per-sub-agent attribution on orders.

## Agents, stores, and sub-agents

- Agents are not only serving end customers; they also onboard **sub-agents**.
- On the **main site**, an agent creates **stores**. Each store has a **unique link** so sub-agents can register or manage their presence under that store.
- **Rule of thumb:** `Platform → Agent → Store → Sub-agent → (optional) end customer`.

## Pricing and margins

- The **platform** sets **base prices** for catalog items (e.g. bundles aligned with `bundle_subscriptions` or future product SKUs).
- **Sub-agents** set resale prices by adding a **profit margin** on top of those base prices.
- **Hard rule:** Sub-agent (store) prices must be **strictly greater than** the base price — **not equal to** and **not below** the owner’s base price. This must be enforced in validation (UI + API) and in any bulk import.

## How this maps to the current codebase (today)

| Concept | Current building blocks | Gap for full SaaS |
|--------|-------------------------|-------------------|
| Base catalog / admin pricing | `BundleSubscription`, admin bundles | Optional: generic `products` if you outgrow bundles-only |
| Agent resale offers | `ResellerPlan`, `UserSubscription` | Link plans to **store** or **sub-agent** when multi-store ships |
| Orders | `Order`, `OrderService`, API `POST /api/v1/orders` | Add `store_id`, `sub_agent_user_id`, `external_ref`, iGate payload |
| Auth / roles | Spatie roles, Sanctum API | Per-agent API tokens, tenant scoping on queries |
| Wallets | `Wallet`, top-ups | Decide if sub-agents have wallets or only agents settle |

## Implementation phases (recommended)

1. **Tenancy light:** Introduce `stores` (belongs to agent user), `slug` / token for **unique onboarding URL**, `sub_agent` role or `users.parent_id` + `store_id`. *(Done in app.)*
2. **Pricing layer:** Table or fields for **per-store or per-sub-agent** selling price per catalog line; server-side check: `selling_price > base_price`. *(Done: `sub_agent_bundle_prices`.)*
3. **Order attribution:** Extend `orders` with store/sub-agent and propagate to admin reporting and API responses. *(Done: sub-agents are debited their **store price**; `store_id` / `sub_agent_user_id` on orders; web, admin, API.)*
4. **API hardening:** Scoped Sanctum tokens, documented webhooks or callbacks if iGate pushes status back. *(Partially done: `access_api` gate, per-route **abilities**, login + per-user throttling; clients must **re-login** after upgrades to get abilities.)*
5. **SaaS commercial:** Plans, quotas (orders/month, API calls), invoicing — outside core order flow but should not block (1)–(3).
6. **iGate outbound:** Map fulfilled orders to iGate payloads / webhooks (per environment). *(Done: config-driven HTTP POST, queued job on **processing**, idempotency key, retries, admin-visible status/error on orders — adjust `IGATE_*` and payload mapping to match real iGate.)*
7. **iGate inbound:** Partner calls **`POST /api/webhooks/igate`** with `IGATE_WEBHOOK_SECRET` as `Authorization: Bearer …` or `X-Wps-Webhook-Secret`; body maps `status` → move order **processing → sent|failed** (idempotent). Optional `refund_wallet` on failure.
8. **Per-user API tokens:** Web UI **API tokens** (permission `access_api`) creates named Sanctum tokens with the same ability set as password login — no shared password across integrations.

## Implementation note

When building store creation, unique links, margin validation, and outbound submission to iGate, **always** enforce the **strictly greater than base** pricing rule and keep **API and web** behavior consistent.
