# Usage Guide

## Quick Start Examples

### 1. Connect Merchant Account

```php
use Ibraheem\AramexIntegration\Models\MerchantAramexAccount;

$account = MerchantAramexAccount::create([
    'merchant_id' => 'merchant_123',
    'aramex_username' => 'username@aramex.com',
    'aramex_password' => 'password',
    'account_number' => '123456',
    'account_pin' => '1234',
    'entity' => 'AMM',
    'country_code' => 'JO',
    'city' => 'Amman',
    'environment' => 'sandbox',
    'is_active' => true,
]);

// Get API key
$apiKey = $account->merchant_api_key;
```

### 2. Create Shipment (Using Facade)

```php
use Ibraheem\AramexIntegration\Facades\AramexIntegration;

$result = AramexIntegration::createShipment('merchant_123', [
    'reference' => 'ORDER-12345',
    'receiver' => [
        'name' => 'Ahmed Ali',
        'phone' => '+966501234567',
        'email' => 'ahmed@example.com',
        'address' => [
            'line1' => '123 Main Street',
            'city' => 'Riyadh',
            'country_code' => 'SA',
            'postal_code' => '12345',
        ],
    ],
    'weight' => 2.5,
    'product_name' => 'Electronics',
    'cod_amount' => 500.00,
]);
```

### 3. Create Shipment (Using Service)

```php
use Ibraheem\AramexIntegration\Services\AramexService;

$aramexService = app('aramex');
$result = $aramexService->createShipment('merchant_123', $data);
```

### 4. Track Shipment

```php
use Ibraheem\AramexIntegration\Facades\AramexIntegration;

$result = AramexIntegration::trackShipment('merchant_123', '1234567890');

// Access tracking data
$status = $result['current_status'];
$history = $result['history'];
```

### 5. Calculate Rate

```php
use Ibraheem\AramexIntegration\Facades\AramexIntegration;

$result = AramexIntegration::calculateRate('merchant_123', [
    'origin' => [
        'line1' => '123 Street',
        'city' => 'Riyadh',
        'country_code' => 'SA',
    ],
    'destination' => [
        'line1' => '456 Street',
        'city' => 'Jeddah',
        'country_code' => 'SA',
    ],
    'weight' => 2.5,
]);

$rate = $result['total_amount'];
$currency = $result['currency'];
```

### 6. Get Billing Information

```php
use Ibraheem\AramexIntegration\Services\BillingService;

$billingService = app('aramex.billing');

// Get remaining free quota
$remaining = $billingService->getRemainingFreeQuota('merchant_123');

// Generate monthly billing
$billing = $billingService->generateMonthlyBilling('merchant_123', 11, 2025);
```

### 7. Listen to Events

```php
use Ibraheem\AramexIntegration\Events\ShipmentCreated;
use Ibraheem\AramexIntegration\Events\ShipmentUpdated;

Event::listen(ShipmentCreated::class, function ($event) {
    // Handle shipment created
    $shipment = $event->shipment;
    // Your custom logic
});

Event::listen(ShipmentUpdated::class, function ($event) {
    // Handle shipment updated
    $shipment = $event->shipment;
    $oldStatus = $event->oldStatus;
    // Your custom logic
});
```

### 8. Access Dashboard

Navigate to:
```
http://your-domain.com/aramex/dashboard?merchant_id=merchant_123&api_key=your-api-key
```

### 9. Use Artisan Commands

```bash
# Sync all shipment statuses
php artisan aramex:sync-status

# Sync for specific merchant
php artisan aramex:sync-status --merchant-id=merchant_123

# Generate monthly billing
php artisan aramex:monthly-billing

# Generate for specific month
php artisan aramex:monthly-billing --month=11 --year=2025
```

### 10. Query Models

```php
use Ibraheem\AramexIntegration\Models\AramexShipment;
use Ibraheem\AramexIntegration\Models\MerchantBilling;
use Ibraheem\AramexIntegration\Models\MerchantTransaction;

// Get shipments
$shipments = AramexShipment::byMerchant('merchant_123')
    ->byStatus('delivered')
    ->get();

// Get billing
$billing = MerchantBilling::byMerchant('merchant_123')
    ->byStatus('pending')
    ->first();

// Get transactions
$transactions = MerchantTransaction::byMerchant('merchant_123')
    ->byType('shipment_fee')
    ->get();
```

## API Usage

### Using API Endpoints

All endpoints require `X-API-KEY` header:

```bash
curl -X POST https://your-domain.com/api/aramex/shipments \
  -H "X-API-KEY: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{...}'
```

### Getting API Key

```php
$account = MerchantAramexAccount::where('merchant_id', 'merchant_123')->first();
$apiKey = $account->merchant_api_key;
```

## Queue Usage

Make sure queue worker is running:

```bash
php artisan queue:work --queue=aramex
```

All shipment creation and SMS sending is automatically queued.

## Webhook Setup

1. Configure webhook URL in Aramex dashboard:
   ```
   https://your-domain.com/api/aramex/webhook/{merchantId}
   ```

2. Webhook will automatically:
   - Log the webhook
   - Update shipment status
   - Fire ShipmentUpdated event

## Best Practices

1. **Always use queue** - Shipment creation is queued by default
2. **Handle errors** - Wrap API calls in try-catch
3. **Monitor logs** - Check logs for API errors
4. **Use events** - Listen to events for custom logic
5. **Validate data** - Always validate before creating shipments
6. **Test in sandbox** - Use sandbox environment for testing
7. **Monitor billing** - Check billing regularly
8. **Keep API keys secure** - Never expose API keys

## Common Patterns

### Pattern 1: Create and Track

```php
// Create shipment
$result = AramexIntegration::createShipment($merchantId, $data);
$trackingNumber = $result['shipment']->tracking_number;

// Track immediately
$tracking = AramexIntegration::trackShipment($merchantId, $trackingNumber);
```

### Pattern 2: Calculate Before Create

```php
// Calculate rate first
$rate = AramexIntegration::calculateRate($merchantId, $rateData);

// If acceptable, create shipment
if ($rate['total_amount'] < $maxCost) {
    $shipment = AramexIntegration::createShipment($merchantId, $shipmentData);
}
```

### Pattern 3: Handle Webhook

```php
// Webhook automatically updates shipment
// Listen to event for custom handling
Event::listen(ShipmentUpdated::class, function ($event) {
    if ($event->shipment->isDelivered()) {
        // Send notification, update order status, etc.
    }
});
```

