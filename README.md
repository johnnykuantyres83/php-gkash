# GKash PHP Gateway

Standalone, PHP 5.6-compatible GKash payment integration rebuilt from the legacy `payment-process-new.php`, `lib/gkash.php`, and `thankyou-gkash.php` flow.

## What is included

- Clean reusable PHP classes
- Card payment support with `preselection=ECOMM`
- eWallet support with `preselection=EWALLET`
- SHA512 signature generation and verification
- Optional payment requery support
- Lightweight daily logging
- Sample checkout form
- Success and failure pages

## Repository layout

- `bootstrap.php` loads config, autoloading, and shared helpers
- `config/gkash.php` stores safe defaults
- `config/gkash.local.php` overrides sensitive values locally
- `src/` contains the reusable gateway classes
- `public/` contains the browser entry points
- `examples/` contains sample integration files
- `cache/` stores order and replay state for the demo flow
- `logs/` stores daily gateway logs

## Legacy file mapping

- `lib/gkash.php` -> `src/GKashClient.php`, `src/GKashCallbackHandler.php`, `src/GKashSignature.php`
- `payment-process-new.php` -> `src/GKashCheckout.php` and `public/checkout.php`
- `thankyou-gkash.php` -> `public/return.php`

## Installation

1. Copy the repository to your web root.
2. Create `config/gkash.local.php` from `config/gkash.example.php`.
3. Fill in your GKash `CID`, `SignatureKey`, and gateway URLs.
4. Point your browser to `public/index.php` or `examples/simple-checkout.php`.

## Configuration

Put sensitive values in `config/gkash.local.php`.

Required fields:

- `cid`
- `signature_key`
- `checkout_endpoint`
- `return_url`
- `callback_url`

Recommended settings:

- `environment`
- `currency`
- `query_endpoint`
- `demo_mode`
- `payment_method`

## Important security note

Do not commit real credentials into source control. Keep `config/gkash.local.php` out of Git and put it in `.gitignore`.

## Payment flow

1. User opens `examples/simple-checkout.php`
2. User fills in name, email, phone, item details, and amount
3. `public/checkout.php` validates and prepares the order
4. If gateway settings are present, the page auto-posts to GKash
5. GKash returns to `public/return.php`
6. GKash posts callback data to `public/callback.php`
7. The callback handler verifies signature and amount
8. The callback result is stored in `cache/orders/`
9. The browser is shown `public/success.php` or `public/failed.php`

## Signature generation

Checkout signature:

`SignatureKey;CID;CartID;AmountWithoutDot;Currency`

Callback signature:

`SignatureKey;CID;POID;CartID;AmountWithoutDot;Currency;Status`

Both strings are uppercased before hashing with SHA512.

## Requery flow

If a callback is unclear or needs extra confirmation, `GKashClient::requeryPaymentStatus()` posts to the configured query endpoint and parses the response as JSON or form-encoded data.

## Sample frontend usage

Open `examples/simple-checkout.php` and submit the form.

Fields supported:

- Name
- Email
- Phone
- Item / Product Name
- Item Description
- Quantity
- Item Total
- Extra Charges
- Final Amount
- Order ID
- Address
- Remark
- Custom Parameter

## Demo mode

If no checkout endpoint is configured, the sample flow falls back to local demo mode so the repository still behaves like a full payment walkthrough without real merchant credentials.

## Logging

Logs are written daily under `logs/`.

- callback logs
- error logs
- debug logs

## Troubleshooting

- If the checkout page never leaves your site, check `checkout_endpoint`
- If callback verification fails, compare `SignatureKey`, `CID`, and amount formatting
- If return page shows waiting, confirm that the callback reached `public/callback.php`
- If requery fails, confirm `query_endpoint` and network access

