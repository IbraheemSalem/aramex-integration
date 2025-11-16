# API Examples

## Complete API Request Examples

### 1. Connect Merchant Account

```bash
curl -X POST https://your-domain.com/api/aramex/account/connect \
  -H "X-API-KEY: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "merchant_id": "merchant_123",
    "aramex_username": "test@aramex.com",
    "aramex_password": "password123",
    "account_number": "123456",
    "account_pin": "1234",
    "entity": "AMM",
    "country_code": "JO",
    "city": "Amman",
    "environment": "sandbox",
    "is_active": true
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Aramex account connected successfully",
  "data": {
    "id": 1,
    "merchant_id": "merchant_123",
    "merchant_api_key": "generated-key-64-chars",
    "aramex_username": "test@aramex.com",
    "account_number": "123456",
    "entity": "AMM",
    "country_code": "JO",
    "city": "Amman",
    "environment": "sandbox",
    "is_active": true
  }
}
```

### 2. Create Shipment

```bash
curl -X POST https://your-domain.com/api/aramex/shipments \
  -H "X-API-KEY: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "reference": "ORDER-12345",
    "receiver": {
      "name": "Ahmed Ali",
      "phone": "+966501234567",
      "email": "ahmed@example.com",
      "address": {
        "line1": "123 Main Street",
        "line2": "Building 5, Apartment 10",
        "city": "Riyadh",
        "country_code": "SA",
        "postal_code": "12345",
        "state": "Riyadh"
      },
      "contact": {
        "company": "ABC Company",
        "department": "Sales",
        "type": "Business"
      }
    },
    "weight": 2.5,
    "weight_unit": "KG",
    "product_name": "Electronics - Laptop",
    "cod_amount": 500.00,
    "cod_currency": "SAR",
    "payment_type": "C",
    "product_type": "ONX",
    "product_group": "DOM",
    "number_of_pieces": 1,
    "items": [
      {
        "package_type": "Box",
        "quantity": 1,
        "weight": 2.5,
        "weight_unit": "KG",
        "description": "Laptop Computer",
        "reference": "ITEM-001"
      }
    ],
    "shipper_address": {
      "line1": "456 Warehouse St",
      "city": "Jeddah",
      "country_code": "SA",
      "postal_code": "21461"
    },
    "shipper_contact": {
      "name": "Merchant Name",
      "phone": "+966502345678",
      "email": "merchant@example.com"
    }
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Shipment creation queued",
  "data": {
    "message": "Shipment creation queued successfully",
    "merchant_id": "merchant_123"
  }
}
```

### 3. Track Shipment

```bash
curl -X GET "https://your-domain.com/api/aramex/shipments/track/1234567890" \
  -H "X-API-KEY: your-api-key"
```

**Response:**
```json
{
  "success": true,
  "message": "Tracking information retrieved successfully",
  "data": {
    "tracking_number": "1234567890",
    "current_status": "in_transit",
    "status_code": "SH003",
    "status_description": "In Transit",
    "history": [
      {
        "date": "2025-11-15T10:00:00Z",
        "code": "SH001",
        "description": "Shipment Created",
        "location": "Amman",
        "comments": "Shipment created and ready for pickup"
      },
      {
        "date": "2025-11-15T14:00:00Z",
        "code": "SH002",
        "description": "Picked Up",
        "location": "Amman Warehouse",
        "comments": "Shipment picked up by courier"
      },
      {
        "date": "2025-11-16T08:00:00Z",
        "code": "SH003",
        "description": "In Transit",
        "location": "Riyadh Hub",
        "comments": "Shipment in transit to destination"
      }
    ],
    "estimated_delivery_date": "2025-11-17T12:00:00Z"
  }
}
```

### 4. Calculate Rate

```bash
curl -X POST https://your-domain.com/api/aramex/rate/calculate \
  -H "X-API-KEY: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "origin": {
      "line1": "123 Warehouse Street",
      "city": "Riyadh",
      "country_code": "SA",
      "postal_code": "11564"
    },
    "destination": {
      "line1": "456 Main Street",
      "city": "Jeddah",
      "country_code": "SA",
      "postal_code": "21461"
    },
    "weight": 2.5,
    "weight_unit": "KG",
    "dimensions": {
      "length": 30,
      "width": 20,
      "height": 15,
      "unit": "CM"
    },
    "product_type": "ONX",
    "product_group": "DOM",
    "number_of_pieces": 1
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Rate calculated successfully",
  "data": {
    "total_amount": 45.50,
    "currency": "SAR",
    "response": {
      "TotalAmount": {
        "Value": 45.50,
        "CurrencyCode": "SAR"
      }
    }
  }
}
```

### 5. Get Shipments List

```bash
curl -X GET "https://your-domain.com/api/aramex/shipments?status=delivered&per_page=20" \
  -H "X-API-KEY: your-api-key"
```

**Response:**
```json
{
  "success": true,
  "message": "Shipments retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "tracking_number": "1234567890",
        "reference": "ORDER-12345",
        "status": "delivered",
        "weight": 2.5,
        "cod_amount": 500.00,
        "created_at": "2025-11-15T10:00:00.000000Z"
      }
    ],
    "per_page": 20,
    "total": 1
  }
}
```

### 6. Get Billing History

```bash
curl -X GET "https://your-domain.com/api/aramex/billing/history?per_page=10" \
  -H "X-API-KEY: your-api-key"
```

**Response:**
```json
{
  "success": true,
  "message": "Billing history retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "billing_period": "2025-11",
        "month": 11,
        "year": 2025,
        "monthly_subscription_fee": 100.00,
        "total_shipments": 25,
        "free_shipments_used": 10,
        "paid_shipments": 15,
        "per_shipment_fee": 5.00,
        "total_amount": 175.00,
        "currency": "SAR",
        "status": "pending",
        "created_at": "2025-11-01T00:00:00.000000Z"
      }
    ]
  }
}
```

### 7. Get Free Quota Status

```bash
curl -X GET "https://your-domain.com/api/aramex/billing/free-quota" \
  -H "X-API-KEY: your-api-key"
```

**Response:**
```json
{
  "success": true,
  "message": "Free quota retrieved successfully",
  "data": {
    "total_quota": 10,
    "remaining_quota": 3,
    "used_quota": 7
  }
}
```

### 8. Webhook Example

Aramex will send webhooks to:
```
POST https://your-domain.com/api/aramex/webhook/{merchantId}
```

**Webhook Payload:**
```json
{
  "WaybillNumber": "1234567890",
  "UpdateCode": "SH005",
  "UpdateDescription": "Delivered",
  "UpdateDateTime": "2025-11-17T12:00:00Z",
  "UpdateLocation": "Riyadh",
  "Comments": "Delivered to recipient"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Webhook processed successfully",
  "data": {
    "id": 1,
    "tracking_number": "1234567890",
    "status": "delivered",
    "last_tracking_update": "2025-11-17T12:00:00.000000Z"
  }
}
```

## Error Responses

All errors follow this format:

```json
{
  "success": false,
  "message": "Error description",
  "error_code": "ARAMEX_ERROR_01",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### Common Error Codes

- `ARAMEX_API_KEY_MISSING` - API key not provided
- `ARAMEX_API_KEY_INVALID` - Invalid API key
- `ARAMEX_ACCOUNT_INACTIVE` - Account is not active
- `ARAMEX_VALIDATION_01` - Validation error
- `ARAMEX_ACCOUNT_NOT_FOUND` - Account not found
- `ARAMEX_SHIPMENT_ERROR` - Shipment creation failed
- `ARAMEX_TRACK_ERROR` - Tracking failed
- `ARAMEX_RATE_ERROR` - Rate calculation failed
- `ARAMEX_WEBHOOK_ERROR` - Webhook processing failed

