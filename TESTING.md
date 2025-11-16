# Testing Guide

## Setup Testing Environment

### 1. Environment Configuration

Add to `.env.testing`:

```env
ARAMEX_SANDBOX_URL=https://ws.dev.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc/json
ARAMEX_ENVIRONMENT=sandbox
ARAMEX_SMS_ENABLED=false
```

### 2. Test Database

Use separate test database:

```env
DB_DATABASE=aramex_test
```

## Manual Testing Checklist

### Account Management
- [ ] Connect merchant account
- [ ] Get account details
- [ ] Update account
- [ ] Delete account
- [ ] Verify API key generation

### Shipment Creation
- [ ] Create shipment with all required fields
- [ ] Create shipment with COD
- [ ] Create shipment with multiple items
- [ ] Verify queue processing
- [ ] Check label generation
- [ ] Verify event firing

### Tracking
- [ ] Track single shipment
- [ ] Track bulk shipments
- [ ] Verify status updates
- [ ] Check tracking history

### Rate Calculator
- [ ] Calculate rate for domestic shipment
- [ ] Calculate rate for international shipment
- [ ] Verify rate accuracy

### Billing
- [ ] Verify free quota calculation
- [ ] Test per-shipment charging
- [ ] Generate monthly billing
- [ ] Check transaction recording

### SMS
- [ ] Test SMS sending (with test provider)
- [ ] Verify SMS template
- [ ] Check queue processing

### Webhooks
- [ ] Test webhook reception
- [ ] Verify status update
- [ ] Check webhook logging
- [ ] Test event firing

### Commands
- [ ] Test sync-status command
- [ ] Test monthly-billing command
- [ ] Verify command options

## API Testing with Postman

1. Import collection from `API_EXAMPLES.md`
2. Set base URL
3. Set API key in headers
4. Test all endpoints

## Queue Testing

```bash
# Start queue worker
php artisan queue:work --queue=aramex

# Create shipment via API
# Verify job is processed
# Check database for shipment record
```

## Event Testing

```php
// In your test
Event::fake();

// Perform action
AramexIntegration::createShipment($merchantId, $data);

// Assert event was fired
Event::assertDispatched(ShipmentCreated::class);
```

## Database Testing

```php
// Verify migrations
php artisan migrate:status

// Check tables exist
Schema::hasTable('merchant_aramex_accounts');
Schema::hasTable('aramex_shipments');
// etc.
```

## Integration Testing

1. Create test merchant account
2. Create test shipment
3. Track shipment
4. Verify webhook processing
5. Check billing calculation

## Performance Testing

- Test bulk shipment creation
- Test bulk tracking
- Monitor queue processing time
- Check database query performance

## Security Testing

- [ ] Verify API key authentication
- [ ] Test invalid API key rejection
- [ ] Verify sensitive data hiding
- [ ] Test input validation
- [ ] Check SQL injection protection

## Error Handling Testing

- [ ] Test invalid credentials
- [ ] Test network failures
- [ ] Test API errors
- [ ] Verify error logging
- [ ] Check error response format

