# Quick Start Guide

## 5-Minute Setup

### Step 1: Install Package

```bash
cd /var/www/html/v1/urbill
composer require ibraheem/aramex-integration
```

### Step 2: Publish & Migrate

```bash
php artisan vendor:publish --tag=aramex-config
php artisan vendor:publish --tag=aramex-migrations
php artisan migrate
```

### Step 3: Configure

Add to `.env`:

```env
ARAMEX_SANDBOX_URL=https://ws.dev.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc/json
ARAMEX_PRODUCTION_URL=https://ws.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc/json
ARAMEX_MONTHLY_FEE=100.00
ARAMEX_PER_SHIPMENT_FEE=5.00
ARAMEX_FREE_QUOTA=10
```

### Step 4: Create First Account

```php
use Ibraheem\AramexIntegration\Models\MerchantAramexAccount;

$account = MerchantAramexAccount::create([
    'merchant_id' => 'merchant_1',
    'aramex_username' => 'your-username',
    'aramex_password' => 'your-password',
    'account_number' => 'your-account',
    'account_pin' => 'your-pin',
    'entity' => 'AMM',
    'country_code' => 'JO',
    'city' => 'Amman',
    'environment' => 'sandbox',
    'is_active' => true,
]);

echo "API Key: " . $account->merchant_api_key;
```

### Step 5: Create First Shipment

```bash
curl -X POST http://localhost/api/aramex/shipments \
  -H "X-API-KEY: your-api-key-here" \
  -H "Content-Type: application/json" \
  -d '{
    "receiver": {
      "name": "Test User",
      "phone": "+966501234567",
      "email": "test@example.com",
      "address": {
        "line1": "123 Test St",
        "city": "Riyadh",
        "country_code": "SA",
        "postal_code": "12345"
      }
    },
    "weight": 1.0,
    "product_name": "Test Product"
  }'
```

### Step 6: Start Queue Worker

```bash
php artisan queue:work --queue=aramex
```

## That's It! 🎉

Your Aramex integration is ready to use!

## Next Steps

- Read [README.md](README.md) for detailed usage
- Check [API_EXAMPLES.md](API_EXAMPLES.md) for API examples
- Visit dashboard: `/aramex/dashboard?merchant_id=merchant_1&api_key=your-key`

