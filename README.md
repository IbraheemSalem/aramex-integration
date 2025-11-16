# Aramex Integration Package

حزمة Laravel كاملة لتكامل Aramex للشحن مع دعم متعدد التجار، نظام الفواتير، إشعارات SMS، ولوحة تحكم.

Complete Laravel package for Aramex shipping integration with multi-merchant support, billing system, SMS notifications, and dashboard UI.

## 📋 Table of Contents

- [الميزات الرئيسية](#الميزات-الرئيسية)
- [التثبيت](#التثبيت)
- [الإعداد](#الإعداد)
- [الاستخدام](#الاستخدام)
- [API Endpoints](#api-endpoints)
- [الأمثلة](#الأمثلة)
- [الأوامر](#الأوامر)
- [التوثيق الكامل](#التوثيق-الكامل)

## ✨ الميزات الرئيسية

- ✅ **دعم متعدد التجار** - كل تاجر له حساب Aramex منفصل
- ✅ **إنشاء الشحنات** - إنشاء شحنات عبر API مع معالجة Queue
- ✅ **تتبع الشحنات** - تتبع الشحنات الفردية والجماعية
- ✅ **حاسبة الأسعار** - حساب تكلفة الشحن قبل الإنشاء
- ✅ **نظام الفواتير** - فواتير شهرية + رسوم لكل شحنة + حصة مجانية
- ✅ **إشعارات SMS** - إرسال SMS تلقائي عند إنشاء الشحنة
- ✅ **Webhooks** - استقبال تحديثات الحالة من Aramex
- ✅ **لوحة التحكم** - Dashboard كامل لإدارة الشحنات
- ✅ **أوامر Artisan** - مزامنة الحالة وإنشاء الفواتير
- ✅ **نظام Events** - Events و Listeners قابلة للتوسع

## 📦 التثبيت

### الخطوة 1: إضافة الحزمة إلى Composer

أضف الحزمة إلى ملف `composer.json` في مشروع Laravel:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/ibraheem/aramex-integration"
        }
    ],
    "require": {
        "ibraheem/aramex-integration": "@dev"
    }
}
```

ثم قم بتشغيل:

```bash
composer require ibraheem/aramex-integration
```

### الخطوة 2: نشر ملفات الإعداد

```bash
# نشر ملف الإعداد
php artisan vendor:publish --tag=aramex-config

# نشر Migrations
php artisan vendor:publish --tag=aramex-migrations

# نشر Views (اختياري - لتخصيص Dashboard)
php artisan vendor:publish --tag=aramex-views

# نشر Public Assets (اختياري)
php artisan vendor:publish --tag=aramex-assets
```

### الخطوة 3: تشغيل Migrations

```bash
php artisan migrate
```

سيتم إنشاء الجداول التالية:
- `merchant_aramex_accounts` - حسابات التجار
- `aramex_shipments` - الشحنات
- `merchant_billings` - الفواتير الشهرية
- `merchant_transactions` - المعاملات
- `webhook_logs` - سجلات Webhooks

## ⚙️ الإعداد (Configuration)

### الخطوة 4: إعداد متغيرات البيئة

أضف المتغيرات التالية إلى ملف `.env`:

```env
# ============================================
# Aramex API Configuration
# ============================================
ARAMEX_SANDBOX_URL=https://ws.dev.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc/json
ARAMEX_PRODUCTION_URL=https://ws.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc/json
ARAMEX_API_TIMEOUT=60

# ============================================
# Billing Configuration
# ============================================
ARAMEX_MONTHLY_FEE=100.00          # رسوم الاشتراك الشهرية
ARAMEX_PER_SHIPMENT_FEE=5.00       # رسوم كل شحنة
ARAMEX_FREE_QUOTA=10               # عدد الشحنات المجانية الشهرية
ARAMEX_CURRENCY=SAR                # العملة

# ============================================
# SMS Configuration (اختياري)
# ============================================
ARAMEX_SMS_ENABLED=true
ARAMEX_SMS_PROVIDER=twilio         # twilio أو nexmo
ARAMEX_SMS_FROM=Aramex

# Twilio Configuration (إذا كنت تستخدم Twilio)
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM=your_twilio_number

# Nexmo Configuration (إذا كنت تستخدم Nexmo)
NEXMO_KEY=your_nexmo_key
NEXMO_SECRET=your_nexmo_secret
NEXMO_FROM=your_nexmo_from

# ============================================
# Webhook Configuration
# ============================================
ARAMEX_WEBHOOK_SECRET=your-webhook-secret-key

# ============================================
# Queue Configuration
# ============================================
ARAMEX_QUEUE_CONNECTION=default
ARAMEX_QUEUE_NAME=aramex
```

### الخطوة 5: إعداد Queue Worker

يجب تشغيل Queue Worker لمعالجة إنشاء الشحنات وإرسال SMS:

```bash
php artisan queue:work --queue=aramex
```

أو إضافته إلى Supervisor للإنتاج:

```ini
[program:aramex-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=aramex --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/logs/aramex-queue.log
```

### الخطوة 6: إعداد الأوامر المجدولة (اختياري)

أضف إلى `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // مزامنة حالة الشحنات يومياً
    $schedule->command('aramex:sync-status')->daily();
    
    // إنشاء الفواتير الشهرية في أول كل شهر
    $schedule->command('aramex:monthly-billing')->monthlyOn(1, '00:00');
}
```

### الخطوة 7: التحقق من التثبيت

```bash
# التحقق من Routes
php artisan route:list | grep aramex

# التحقق من Commands
php artisan list | grep aramex
```

## 🚀 الاستخدام (Usage)

### 1. ربط حساب التاجر (Connect Merchant Account)

#### الطريقة الأولى: عبر Model مباشرة

```php
use Ibraheem\AramexIntegration\Models\MerchantAramexAccount;

$account = MerchantAramexAccount::create([
    'merchant_id' => 'merchant_123',              // معرف التاجر الفريد
    'aramex_username' => 'username@aramex.com',  // اسم مستخدم Aramex
    'aramex_password' => 'password',              // كلمة مرور Aramex
    'account_number' => '123456',                 // رقم حساب Aramex
    'account_pin' => '1234',                      // PIN الحساب
    'entity' => 'AMM',                            // Entity (مثل: AMM, BAH, etc.)
    'country_code' => 'JO',                       // رمز الدولة (2 أحرف)
    'city' => 'Amman',                            // المدينة
    'environment' => 'sandbox',                   // sandbox أو production
    'is_active' => true,                          // تفعيل الحساب
]);

// الحصول على API Key (يتم إنشاؤه تلقائياً)
$apiKey = $account->merchant_api_key;
echo "API Key: " . $apiKey;
```

#### الطريقة الثانية: عبر API

```bash
curl -X POST http://your-domain.com/api/aramex/account/connect \
  -H "X-API-KEY: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "merchant_id": "merchant_123",
    "aramex_username": "username@aramex.com",
    "aramex_password": "password",
    "account_number": "123456",
    "account_pin": "1234",
    "entity": "AMM",
    "country_code": "JO",
    "city": "Amman",
    "environment": "sandbox",
    "is_active": true
  }'
```

### 2. إنشاء شحنة (Create Shipment)

#### استخدام Facade

```php
use Ibraheem\AramexIntegration\Facades\AramexIntegration;

$result = AramexIntegration::createShipment('merchant_123', [
    'reference' => 'ORDER-12345',              // رقم مرجعي للشحنة
    'receiver' => [
        'name' => 'Ahmed Ali',
        'phone' => '+966501234567',
        'email' => 'ahmed@example.com',
        'address' => [
            'line1' => '123 Main Street',
            'line2' => 'Building 5',            // اختياري
            'city' => 'Riyadh',
            'country_code' => 'SA',
            'postal_code' => '12345',
            'state' => 'Riyadh',               // اختياري
        ],
        'contact' => [                         // اختياري
            'company' => 'ABC Company',
            'department' => 'Sales',
            'type' => 'Business',
        ],
    ],
    'weight' => 2.5,                           // الوزن بالكيلو
    'weight_unit' => 'KG',                     // KG أو LB
    'product_name' => 'Electronics - Laptop',
    'cod_amount' => 500.00,                    // مبلغ COD (اختياري)
    'cod_currency' => 'SAR',                   // عملة COD
    'payment_type' => 'C',                     // C للCOD، P للدفع المسبق
    'product_type' => 'ONX',                   // نوع المنتج
    'product_group' => 'DOM',                  // DOM للمحلي، EXP للدولي
    'number_of_pieces' => 1,
    'items' => [                               // اختياري - قائمة العناصر
        [
            'package_type' => 'Box',
            'quantity' => 1,
            'weight' => 2.5,
            'weight_unit' => 'KG',
            'description' => 'Laptop Computer',
            'reference' => 'ITEM-001',
        ],
    ],
]);

// النتيجة
if ($result['success']) {
    $shipment = $result['shipment'];
    $trackingNumber = $shipment->tracking_number;
    $labelUrl = $shipment->label_url;
    
    echo "تم إنشاء الشحنة بنجاح!";
    echo "رقم التتبع: " . $trackingNumber;
}
```

**ملاحظة:** إنشاء الشحنة يتم عبر Queue، لذا قد يستغرق بضع ثوانٍ.

### 3. تتبع شحنة (Track Shipment)

```php
use Ibraheem\AramexIntegration\Facades\AramexIntegration;

$result = AramexIntegration::trackShipment('merchant_123', '1234567890');

if ($result['success']) {
    $status = $result['current_status'];        // الحالة الحالية
    $history = $result['history'];              // تاريخ التتبع
    $estimatedDelivery = $result['estimated_delivery_date'];
    
    echo "الحالة: " . $status;
    echo "تاريخ التسليم المتوقع: " . $estimatedDelivery;
}
```

### 4. حساب السعر (Calculate Rate)

```php
use Ibraheem\AramexIntegration\Facades\AramexIntegration;

$result = AramexIntegration::calculateRate('merchant_123', [
    'origin' => [
        'line1' => '123 Warehouse Street',
        'city' => 'Riyadh',
        'country_code' => 'SA',
        'postal_code' => '11564',
    ],
    'destination' => [
        'line1' => '456 Main Street',
        'city' => 'Jeddah',
        'country_code' => 'SA',
        'postal_code' => '21461',
    ],
    'weight' => 2.5,
    'weight_unit' => 'KG',
    'dimensions' => [                          // اختياري
        'length' => 30,
        'width' => 20,
        'height' => 15,
        'unit' => 'CM',
    ],
    'product_type' => 'ONX',
    'product_group' => 'DOM',
    'number_of_pieces' => 1,
]);

if ($result['success']) {
    $rate = $result['total_amount'];
    $currency = $result['currency'];
    
    echo "السعر: " . $rate . " " . $currency;
}
```

### 5. إدارة الفواتير (Billing Management)

```php
use Ibraheem\AramexIntegration\Services\BillingService;

$billingService = app('aramex.billing');

// الحصول على الحصة المجانية المتبقية
$remaining = $billingService->getRemainingFreeQuota('merchant_123');
echo "الحصة المجانية المتبقية: " . $remaining;

// إنشاء فاتورة شهرية
$billing = $billingService->generateMonthlyBilling('merchant_123', 11, 2025);
echo "إجمالي الفاتورة: " . $billing->total_amount;
```

## 📡 API Endpoints

### Base URL
All endpoints are prefixed with `/api/aramex`

### Authentication
Include `X-API-KEY` header with merchant API key.

### Endpoints

#### Account Management
- `POST /api/aramex/account/connect` - Connect account
- `GET /api/aramex/account` - Get account
- `PUT /api/aramex/account` - Update account
- `DELETE /api/aramex/account` - Delete account

#### Shipments
- `POST /api/aramex/shipments` - Create shipment (queued)
- `GET /api/aramex/shipments` - List shipments
- `GET /api/aramex/shipments/track/{trackingNumber}` - Track shipment

#### Rate Calculator
- `POST /api/aramex/rate/calculate` - Calculate shipping rate

#### Billing
- `GET /api/aramex/billing/history` - Get billing history
- `GET /api/aramex/billing/transactions` - Get transactions
- `GET /api/aramex/billing/free-quota` - Get free quota status

#### Webhooks
- `POST /api/aramex/webhook/{merchantId?}` - Webhook endpoint

## 📝 Example API Requests

### Create Shipment

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
        "city": "Riyadh",
        "country_code": "SA",
        "postal_code": "12345"
      }
    },
    "weight": 2.5,
    "product_name": "Electronics",
    "cod_amount": 500.00
  }'
```

### Track Shipment

```bash
curl -X GET "https://your-domain.com/api/aramex/shipments/track/1234567890" \
  -H "X-API-KEY: your-api-key"
```

### Calculate Rate

```bash
curl -X POST https://your-domain.com/api/aramex/rate/calculate \
  -H "X-API-KEY: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "origin": {
      "line1": "123 Street",
      "city": "Riyadh",
      "country_code": "SA"
    },
    "destination": {
      "line1": "456 Street",
      "city": "Jeddah",
      "country_code": "SA"
    },
    "weight": 2.5
  }'
```

## 🔔 Webhooks

### Webhook Payload Example

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

### Configure Webhook URL

Set webhook URL in Aramex dashboard:
```
https://your-domain.com/api/aramex/webhook/{merchantId}
```

## 🎯 Artisan Commands

### Sync Shipment Status

```bash
# Sync all pending shipments
php artisan aramex:sync-status

# Sync for specific merchant
php artisan aramex:sync-status --merchant-id=merchant_123

# Limit number of shipments
php artisan aramex:sync-status --limit=50
```

### Generate Monthly Billing

```bash
# Generate for current month
php artisan aramex:monthly-billing

# Generate for specific month
php artisan aramex:monthly-billing --month=11 --year=2025

# Generate for specific merchant
php artisan aramex:monthly-billing --merchant-id=merchant_123
```

## 📊 Billing System

### Features
- Monthly subscription fee
- Per-shipment fee
- Free shipment quota (configurable)
- Automatic transaction recording
- Billing history tracking

### Free Quota
Each merchant gets a free shipment quota per month (default: 10). After quota is exhausted, each shipment is charged.

## 📱 SMS Notifications

### Configuration
Configure SMS provider in `.env`:

```env
ARAMEX_SMS_ENABLED=true
ARAMEX_SMS_PROVIDER=twilio  # or nexmo
```

### Supported Providers
- Twilio
- Nexmo

SMS is automatically sent when shipment is created (queued).

## 🎨 لوحة التحكم (Dashboard)

يمكن الوصول للوحة التحكم عبر:

```
http://your-domain.com/aramex/dashboard?merchant_id=merchant_123&api_key=your-api-key
```

### الميزات:
- ✅ ربط حساب Aramex
- ✅ إنشاء شحنات
- ✅ تتبع الشحنات
- ✅ حاسبة الأسعار
- ✅ تاريخ الفواتير والمعاملات
- ✅ عرض الحصة المجانية المتبقية

### استخدام Dashboard:

1. افتح الرابط أعلاه في المتصفح
2. أدخل `merchant_id` و `api_key` في URL
3. استخدم النماذج لإنشاء الشحنات وإدارتها

## 📦 Events & Listeners

### Events
- `ShipmentCreated` - Fired when shipment is created
- `ShipmentUpdated` - Fired when shipment status changes
- `BillingCharged` - Fired when billing transaction is created

### Listeners
- `SendShipmentSMS` - Sends SMS notification
- `RecordBillingTransaction` - Records billing transaction

## 🔧 Queue Jobs

All shipment creation and SMS sending is queued:
- `ProcessShipment` - Processes shipment creation
- `SendSMSJob` - Sends SMS notification

Make sure queue worker is running:
```bash
php artisan queue:work
```

## 📚 Models

- `MerchantAramexAccount` - Merchant Aramex credentials
- `AramexShipment` - Shipment records
- `MerchantBilling` - Monthly billing records
- `MerchantTransaction` - Transaction records
- `WebhookLog` - Webhook logs

## 🔐 Security

- API key authentication required for all endpoints (except webhooks)
- Sensitive data (passwords, PINs) are hidden in responses
- Webhook signature verification (implement in your app)

## 📚 التوثيق الكامل

للحصول على توثيق أكثر تفصيلاً، راجع الملفات التالية:

- **[INSTALLATION.md](INSTALLATION.md)** - دليل التثبيت التفصيلي خطوة بخطوة
- **[API_EXAMPLES.md](API_EXAMPLES.md)** - أمثلة كاملة لجميع API endpoints
- **[USAGE.md](USAGE.md)** - دليل الاستخدام الشامل مع أمثلة متقدمة
- **[QUICK_START.md](QUICK_START.md)** - دليل البدء السريع في 5 دقائق
- **[TESTING.md](TESTING.md)** - دليل الاختبار
- **[PACKAGE_SUMMARY.md](PACKAGE_SUMMARY.md)** - ملخص شامل للحزمة

## 🔍 هيكل الحزمة

```
packages/ibraheem/aramex-integration/
├── composer.json                    # إعدادات Composer
├── README.md                        # هذا الملف
├── INSTALLATION.md                  # دليل التثبيت
├── API_EXAMPLES.md                  # أمثلة API
├── USAGE.md                         # دليل الاستخدام
├── QUICK_START.md                   # البدء السريع
├── TESTING.md                       # دليل الاختبار
├── config/
│   └── aramex.php                  # ملف الإعداد
├── database/
│   └── migrations/                 # 5 Migrations
├── routes/
│   ├── api.php                     # API Routes
│   └── web.php                     # Web Routes (Dashboard)
└── src/
    ├── Models/                     # 5 Models
    ├── Services/                   # 3 Services
    ├── Controllers/                # 5 Controllers
    ├── Events/                     # 3 Events
    ├── Listeners/                  # 2 Listeners
    ├── Jobs/                       # 2 Jobs
    ├── Console/                    # 2 Commands
    └── Resources/
        └── views/
            └── dashboard/          # Dashboard UI
```

## 📊 إحصائيات الحزمة

- **عدد ملفات PHP:** 28 ملف
- **عدد Models:** 5
- **عدد Controllers:** 5
- **عدد Services:** 3
- **عدد Migrations:** 5
- **عدد Routes:** 10+ endpoints
- **عدد Events:** 3
- **عدد Jobs:** 2
- **عدد Commands:** 2

## ⚠️ ملاحظات مهمة

1. **Queue Worker:** يجب تشغيل Queue Worker لمعالجة الشحنات
2. **API Keys:** كل تاجر له API Key فريد يتم إنشاؤه تلقائياً
3. **Sandbox:** استخدم Sandbox للاختبار قبل الانتقال للإنتاج
4. **Webhooks:** قم بإعداد Webhook URL في لوحة تحكم Aramex
5. **Billing:** الفواتير الشهرية يتم إنشاؤها تلقائياً أو يدوياً عبر Command

## 🐛 استكشاف الأخطاء

### المشكلة: الشحنة لا تُنشأ
- ✅ تأكد من تشغيل Queue Worker
- ✅ تحقق من صحة بيانات Aramex
- ✅ تأكد من تفعيل الحساب (`is_active = true`)

### المشكلة: SMS لا يُرسل
- ✅ تحقق من إعدادات SMS في `.env`
- ✅ تأكد من صحة بيانات Twilio/Nexmo
- ✅ تحقق من تشغيل Queue Worker

### المشكلة: Webhook لا يعمل
- ✅ تحقق من URL في Aramex dashboard
- ✅ تأكد من أن Route متاح بدون authentication
- ✅ راجع `webhook_logs` table

## 📄 License

MIT License - راجع [LICENSE](LICENSE) للمزيد

## 👥 الدعم

للأسئلة أو المشاكل:
- راجع ملفات التوثيق المذكورة أعلاه
- تحقق من [API_EXAMPLES.md](API_EXAMPLES.md) للأمثلة
- راجع [TESTING.md](TESTING.md) للاختبار

---

**الإصدار:** 1.0.0  
**تاريخ الإصدار:** 2025-11-16  
**الحالة:** ✅ جاهز للإنتاج

