# GKash PHP Gateway Repository — Full Job Scope

Objective:
Rebuild the legacy GKash payment integration into a clean standalone PHP 5.6-compatible repository for showcase and reusable integration.

Analyze old project files:
- payment-process-new.php
- lib/gkash.php
- thankyou-gkash.php

Ignore:
- PayPal
- MOLPay
- iPay88
- Offline payment
- Webcash

Repository name:
gkash-php

Requirements:
- PHP 5.6 compatible
- No Composer dependency required
- No framework dependency
- Clean reusable structure
- Similar style to Lazada/Shopee/TikTok API repos

--------------------------------------------------
TARGET REPOSITORY STRUCTURE
--------------------------------------------------

gkash-php/
├── bootstrap.php
├── config/
│   ├── gkash.php
│   └── gkash.example.php
├── src/
│   ├── GKashClient.php
│   ├── GKashCheckout.php
│   ├── GKashCallbackHandler.php
│   ├── GKashSignature.php
│   ├── GKashResponse.php
│   └── Support/
│       ├── Logger.php
│       └── HttpClient.php
├── public/
│   ├── index.php
│   ├── checkout.php
│   ├── callback.php
│   ├── return.php
│   ├── success.php
│   └── failed.php
├── examples/
│   ├── simple-checkout.php
│   ├── callback-example.php
│   └── order-integration.php
├── cache/
├── logs/
├── README.md
├── LICENSE
└── .gitignore

--------------------------------------------------
SENSITIVE CONFIG REQUIREMENT
--------------------------------------------------

Move all hardcoded GKash variables into:

config/gkash.php

Legacy variables discovered:

$gkash_SignatureKey
$gkash_cid
$gkash_url
$gkash_endpoint
$gkash_version

Do NOT hardcode credentials anywhere in source files.

Support:
- sandbox/live environment
- configurable URLs
- configurable callback URL
- configurable return URL
- configurable log path

Support optional:
config/gkash.local.php

Priority:
1. gkash.local.php
2. gkash.php

--------------------------------------------------
PAYMENT FEATURES
--------------------------------------------------

Support:
1. Card payment
   preselection=ECOMM

2. eWallet payment
   preselection=EWALLET

--------------------------------------------------
SIGNATURE GENERATION
--------------------------------------------------

Implement SHA512 signature generation.

Legacy format:

SignatureKey;CID;CartID;AmountWithoutDot;Currency

Generate:
hash('sha512', $signatureString)

--------------------------------------------------
CALLBACK VERIFICATION
--------------------------------------------------

Verify callback signature using:

SignatureKey;CID;POID;CartID;AmountWithoutDot;Currency;Status

Support successful payment status:
88 = Transferred

--------------------------------------------------
STRUCTURED CALLBACK RESPONSE
--------------------------------------------------

Do NOT directly update database.

Return structured response array/object:

[
    'success' => true,
    'status' => '88',
    'order_id' => 'ORDER1001',
    'transaction_id' => 'PO123456',
    'amount' => '10.00',
    'currency' => 'MYR',
    'signature_valid' => true,
]

--------------------------------------------------
REQUERY SUPPORT
--------------------------------------------------

Add optional server-to-server requery support.

Features:
- verify payment status
- parse response
- timeout handling
- error handling

--------------------------------------------------
LOGGING
--------------------------------------------------

Implement lightweight logger.

Support:
- callback logs
- error logs
- debug logs
- daily log file

--------------------------------------------------
SECURITY
--------------------------------------------------

Implement:
- signature verification
- amount verification
- callback validation
- sanitization
- replay protection helper

README must mention:
- do not commit real credentials
- use local config override
- add sensitive config into .gitignore

--------------------------------------------------
IMPORTANT — SAMPLE FRONTEND REQUIRED
--------------------------------------------------

Build a FULL WORKING SAMPLE PAYMENT FLOW.

Provide a demo frontend page where user can input:

- Name
- Email
- Phone
- Item/Product Name
- Item Description
- Quantity
- Item Total
- Extra Charges
- Final Amount
- Order ID

Optional fields:
- Address
- Remark
- Custom parameter

--------------------------------------------------
FRONTEND FLOW REQUIRED
--------------------------------------------------

Flow:

1. User opens sample checkout form
2. User fills form
3. Submit payment
4. Auto redirect to GKash
5. Complete payment
6. GKash callback received
7. Verify signature
8. Save callback log
9. Redirect user to:
   - success.php
   - failed.php

--------------------------------------------------
SUCCESS PAGE REQUIRED
--------------------------------------------------

success.php must display:

- Payment success message
- Order ID
- Transaction ID
- Amount
- Currency
- Customer name
- Payment status
- Raw callback response (debug mode optional)

--------------------------------------------------
FAILED PAGE REQUIRED
--------------------------------------------------

failed.php must display:

- Payment failed message
- Status code
- Order ID
- Error details if available

--------------------------------------------------
README.md REQUIREMENTS
--------------------------------------------------

Document:
- installation
- configuration
- sandbox setup
- payment flow
- callback flow
- signature generation
- signature verification
- requery flow
- frontend sample usage
- troubleshooting
- security notes
- legacy file mapping

--------------------------------------------------
LEGACY FILE MAPPING
--------------------------------------------------

Example:

Old File
→ New Component

lib/gkash.php
→ src/GKashClient.php

payment-process-new.php
→ src/GKashCheckout.php

thankyou-gkash.php
→ public/return.php

--------------------------------------------------
VALIDATION REQUIRED
--------------------------------------------------

After completion:

1. Run php -l against all PHP files
2. Verify no syntax errors
3. Verify sandbox payment flow
4. Verify callback verification works
5. Verify sample frontend works
6. Verify success/failed redirect flow
7. Verify logging works

--------------------------------------------------
FINAL DELIVERABLES
--------------------------------------------------

Provide:
- complete repository
- all source files
- README.md
- sample frontend
- callback demo
- php lint results
- security notes
- migration notes