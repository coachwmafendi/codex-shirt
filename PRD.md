# PRD: I love Codex T-Shirt Landing Page & Checkout

## 1. Ringkasan Produk

Produk ini ialah single-product e-commerce page untuk menjual **I love Codex T-Shirt**. Sistem akan mempunyai landing page, checkout form, pembayaran melalui **ToyyibPay** dan **Stripe**, serta thank-you page selepas pembayaran.

Produk awal:

| Item | Detail |
|---|---|
| Nama produk | I love Codex T-Shirt |
| Jenis | Physical product |
| Warna | Black |
| Design | White text: “I love Codex.” |
| Harga | RM49.00 |
| Saiz | S, M, L, XL, XXL |
| Payment gateway | ToyyibPay, Stripe |

---

## 2. Objektif

Objektif utama ialah membina flow jualan yang ringkas, jelas, dan mudah diuruskan untuk satu produk T-shirt.

Matlamat:

1. Paparkan produk dengan landing page yang menarik.
2. Benarkan pelanggan memilih saiz dan kuantiti.
3. Kumpul maklumat pelanggan dan alamat penghantaran.
4. Benarkan pelanggan memilih ToyyibPay atau Stripe.
5. Cipta order dengan status `pending` sebelum redirect ke payment gateway.
6. Terima callback/webhook daripada gateway.
7. Update order kepada `paid`, `failed`, atau `cancelled`.
8. Paparkan thank-you page selepas pembayaran.

---

## 3. Skop Versi Pertama

### Dalam Skop

- Single landing page produk.
- Checkout page.
- Order creation.
- Saiz T-shirt: S, M, L, XL, XXL.
- Kuantiti produk.
- Maklumat pelanggan.
- Maklumat alamat penghantaran.
- Pilihan payment gateway: ToyyibPay / Stripe.
- Payment redirect.
- Payment return page.
- Payment webhook/callback.
- Thank-you page.
- Basic order status tracking.

### Luar Skop Untuk Versi Pertama

- Cart multi-product.
- Inventory management penuh.
- Coupon code.
- Login customer.
- Admin dashboard penuh.
- Shipping tracking integration.
- Refund automation.
- Email marketing automation.
- Multi-currency pricing.

---

## 4. Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel |
| Frontend interactive | Livewire 4 |
| UI | Blade, Tailwind, Flux UI jika guna starter kit |
| Database | MySQL atau SQLite untuk development |
| Payment MY | ToyyibPay |
| Payment international | Stripe Checkout |
| Deployment | VPS / Laravel Forge / Ploi / shared hosting yang support Laravel |

---

## 5. User Flow

### 5.1 Landing Page Flow

1. Pelanggan buka `/`.
2. Pelanggan nampak gambar produk, nama produk, harga, benefit, dan CTA.
3. Pelanggan klik **Buy Now**.
4. Sistem bawa pelanggan ke `/checkout`.

### 5.2 Checkout Flow

1. Pelanggan isi nama, email, telefon.
2. Pelanggan isi alamat penghantaran.
3. Pelanggan pilih saiz.
4. Pelanggan pilih kuantiti.
5. Pelanggan pilih gateway: ToyyibPay atau Stripe.
6. Pelanggan klik **Pay Now**.
7. Sistem validate form.
8. Sistem create order dengan status `pending`.
9. Sistem redirect pelanggan ke payment gateway.

### 5.3 Payment Success Flow

1. Payment gateway proses bayaran.
2. Gateway redirect pelanggan ke return URL.
3. Gateway hantar webhook/callback ke sistem.
4. Sistem verify payment.
5. Sistem update order kepada `paid`.
6. Pelanggan dibawa ke `/thank-you/{order}`.

### 5.4 Payment Failed / Cancel Flow

1. Pelanggan cancel payment atau payment gagal.
2. Gateway redirect ke cancel/return URL.
3. Sistem update status jika gateway confirm failed/cancelled.
4. Pelanggan nampak mesej pembayaran tidak berjaya.
5. Pelanggan boleh cuba bayar semula.

---

## 6. Pages & Routes

### 6.1 Routes

```php
Route::livewire('/', 'pages::landing')
    ->name('landing');

Route::livewire('/checkout', 'pages::checkout')
    ->name('checkout');

Route::livewire('/thank-you/{order}', 'pages::thank-you')
    ->name('thank-you');

Route::get('/payment/stripe/success', StripeSuccessController::class)
    ->name('payment.stripe.success');

Route::get('/payment/stripe/cancel', StripeCancelController::class)
    ->name('payment.stripe.cancel');

Route::post('/webhook/stripe', StripeWebhookController::class)
    ->name('webhook.stripe');

Route::get('/payment/toyyibpay/return', ToyyibPayReturnController::class)
    ->name('payment.toyyibpay.return');

Route::post('/webhook/toyyibpay', ToyyibPayWebhookController::class)
    ->name('webhook.toyyibpay');
```

### 6.2 Page Components

```txt
resources/views/pages/
├── ⚡landing.blade.php
├── ⚡checkout.blade.php
└── ⚡thank-you.blade.php
```

### 6.3 Blade Components

```txt
resources/views/components/product/
├── hero.blade.php
├── feature-list.blade.php
├── price-card.blade.php
└── size-guide.blade.php
```

Optional:

```txt
resources/views/components/checkout/
├── order-summary.blade.php
├── gateway-selector.blade.php
└── form-section.blade.php
```

---

## 7. Landing Page Requirements

Landing page perlu mengandungi:

1. Product image.
2. Product name.
3. Price.
4. CTA button.
5. Product description.
6. Size availability.
7. Payment trust indicators.
8. Shipping note.
9. FAQ ringkas.

### Suggested Copy

Headline:

> I love Codex.

Subheadline:

> A clean black tee for builders, coders, and people who ship.

CTA:

> Buy Now — RM49

Product highlights:

- Black T-shirt with bold white print.
- Comfortable daily wear.
- Available in S, M, L, XL, XXL.
- Secure checkout via ToyyibPay or Stripe.

---

## 8. Checkout Requirements

Checkout page perlu ada input berikut:

### Customer Details

| Field | Required | Validation |
|---|---:|---|
| Name | Yes | string, max 255 |
| Email | Yes | valid email |
| Phone | Yes | string, max 30 |

### Shipping Details

| Field | Required | Validation |
|---|---:|---|
| Address line 1 | Yes | string |
| Address line 2 | No | string, nullable |
| City | Yes | string |
| State | Yes | string |
| Postcode | Yes | string |
| Country | Yes | default Malaysia |

### Product Options

| Field | Required | Validation |
|---|---:|---|
| Size | Yes | S, M, L, XL, XXL |
| Quantity | Yes | integer, min 1, max 10 |

### Payment

| Field | Required | Validation |
|---|---:|---|
| Gateway | Yes | toyyibpay, stripe |

---

## 9. Database Design

### 9.1 orders Table

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();

    $table->string('order_number')->unique();

    $table->string('customer_name');
    $table->string('customer_email');
    $table->string('customer_phone');

    $table->string('shipping_address_line_1');
    $table->string('shipping_address_line_2')->nullable();
    $table->string('shipping_city');
    $table->string('shipping_state');
    $table->string('shipping_postcode');
    $table->string('shipping_country')->default('Malaysia');

    $table->string('product_name');
    $table->string('product_sku')->nullable();
    $table->string('size');
    $table->unsignedInteger('quantity')->default(1);
    $table->unsignedInteger('unit_price');
    $table->unsignedInteger('total_amount');

    $table->string('payment_gateway');
    $table->string('payment_status')->default('pending');

    $table->string('stripe_session_id')->nullable();
    $table->string('stripe_payment_intent_id')->nullable();

    $table->string('toyyibpay_bill_code')->nullable();
    $table->string('toyyibpay_transaction_id')->nullable();

    $table->timestamp('paid_at')->nullable();

    $table->timestamps();
});
```

### 9.2 Payment Status Values

```txt
pending
paid
failed
cancelled
expired
```

### 9.3 Payment Gateway Values

```txt
toyyibpay
stripe
```

---

## 10. Payment Architecture

### 10.1 Service Classes

```txt
app/Services/Payments/
├── StripePaymentService.php
└── ToyyibPayService.php
```

### 10.2 Checkout Component Responsibility

The checkout page should:

1. Validate customer input.
2. Calculate total amount.
3. Create order.
4. Call selected payment service.
5. Redirect to payment gateway.

The checkout page should not contain raw payment API logic.

### 10.3 Stripe Flow

1. Create order pending.
2. Create Stripe Checkout Session.
3. Store `stripe_session_id`.
4. Redirect customer to Stripe Checkout.
5. Stripe sends webhook.
6. Webhook verifies event signature.
7. Update order to `paid` when `checkout.session.completed` is received.

### 10.4 ToyyibPay Flow

1. Create order pending.
2. Create ToyyibPay bill.
3. Store `toyyibpay_bill_code`.
4. Redirect customer to ToyyibPay bill page.
5. ToyyibPay sends callback/return.
6. System verifies payment status.
7. Update order to `paid` if payment is successful.

---

## 11. Environment Variables

```env
PRODUCT_NAME="I love Codex T-Shirt"
PRODUCT_PRICE=4900

STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

TOYYIBPAY_SECRET_KEY=
TOYYIBPAY_CATEGORY_CODE=
TOYYIBPAY_BASE_URL=https://toyyibpay.com
```

For sandbox/testing, ToyyibPay base URL may be changed depending on the account setup.

---

## 12. Validation Rules

Checkout validation:

```php
[
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'email', 'max:255'],
    'phone' => ['required', 'string', 'max:30'],

    'addressLine1' => ['required', 'string', 'max:255'],
    'addressLine2' => ['nullable', 'string', 'max:255'],
    'city' => ['required', 'string', 'max:100'],
    'state' => ['required', 'string', 'max:100'],
    'postcode' => ['required', 'string', 'max:20'],
    'country' => ['required', 'string', 'max:100'],

    'size' => ['required', 'in:S,M,L,XL,XXL'],
    'quantity' => ['required', 'integer', 'min:1', 'max:10'],
    'gateway' => ['required', 'in:toyyibpay,stripe'],
]
```

---

## 13. Order Number Format

Suggested format:

```txt
CODX-YYYYMMDD-RANDOM
```

Example:

```txt
CODX-20260115-A7K9D2
```

---

## 14. Thank You Page Requirements

Thank-you page should display:

1. Payment status.
2. Order number.
3. Product name.
4. Size.
5. Quantity.
6. Total paid.
7. Customer email.
8. Short delivery message.

Suggested message:

> Thank you for your order. We have received your payment and will process your T-shirt shipment soon.

If payment is still pending:

> Your order has been created. Payment confirmation is still pending. Please refresh this page in a moment.

---

## 15. Security Requirements

1. Do not trust payment status from frontend.
2. Verify Stripe webhook signature.
3. Verify ToyyibPay payment status server-side where possible.
4. Prevent duplicate payment updates.
5. Order should only be marked paid once.
6. Store payment secrets in `.env` only.
7. Do not expose API keys in Blade or JavaScript.
8. Validate all checkout input.
9. Use CSRF protection for local POST routes.
10. Webhook route should follow gateway-specific verification approach.

---

## 16. Admin / Operations For Version 1

No full admin dashboard required in version 1.

For basic operations, orders can be checked in database first.

Optional simple admin page for later:

```txt
/admin/orders
/admin/orders/{order}
```

Admin order list can show:

- Order number.
- Customer name.
- Email.
- Size.
- Quantity.
- Gateway.
- Payment status.
- Paid at.
- Created at.

---

## 17. Success Criteria

Version 1 is considered successful when:

1. User can view product landing page.
2. User can go to checkout.
3. User can submit order form.
4. Order is saved as `pending`.
5. User can pay using ToyyibPay.
6. User can pay using Stripe.
7. Payment webhook/callback can update order status.
8. User can see thank-you page.
9. Duplicate callbacks do not create duplicate paid records.
10. Payment secrets are not exposed publicly.

---

## 18. Suggested Build Phases

### Phase 1: Foundation

- Install Laravel Livewire starter kit.
- Create landing page.
- Add product image.
- Create checkout page UI.
- Create orders table.
- Save pending orders.

### Phase 2: ToyyibPay

- Add ToyyibPay service.
- Create bill.
- Redirect to ToyyibPay.
- Handle return/callback.
- Update order status.

### Phase 3: Stripe

- Install Stripe SDK or Laravel Cashier if needed.
- Create Stripe Checkout Session.
- Redirect to Stripe Checkout.
- Handle Stripe success/cancel.
- Handle Stripe webhook.
- Update order status.

### Phase 4: Polish

- Improve landing page design.
- Improve checkout UX.
- Add loading states.
- Add validation messages.
- Add thank-you page details.
- Add basic admin order listing if needed.

---

## 19. Open Questions

1. Final price: RM49.00 confirmed?
2. Shipping fee included or separate?
3. Malaysia only or international shipping?
4. Do we need stock control per size?
5. Do we need email confirmation after payment?
6. Should Stripe be available for all customers or only international customers?
7. Should ToyyibPay be the default gateway?
8. Is this one-off campaign or permanent product?

