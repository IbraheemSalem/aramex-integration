# دليل التثبيت الكامل - Complete Installation Guide

هذا الدليل يشرح خطوات تثبيت حزمة Aramex Integration بشكل تفصيلي.

This guide explains step-by-step installation of the Aramex Integration package.

## 📋 المتطلبات (Requirements)

- PHP >= 8.1
- Laravel >= 10.0 أو 11.0
- Composer
- MySQL/MariaDB
- Queue Driver (Redis/Database/SQS)

## 📦 الخطوة 1: إضافة الحزمة إلى Composer (Add Package to Composer)

Add the package to your `composer.json`:

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

**ملاحظة:** إذا كانت الحزمة في مجلد `packages/`، تأكد من أن المسار في `composer.json` صحيح.

## 📝 الخطوة 2: نشر ملفات الإعداد (Publish Configuration)

```bash
php artisan vendor:publish --tag=aramex-config
```

سيتم إنشاء ملف `config/aramex.php` في مجلد `config/` لمشروع Laravel.

يمكنك تعديل هذا الملف لتخصيص الإعدادات، أو استخدام متغيرات البيئة في `.env`.

## ⚙️ الخطوة 3: إعداد متغيرات البيئة (Configure Environment Variables)

Add to your `.env` file:

```env
# Aramex API
ARAMEX_SANDBOX_URL=https://ws.dev.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc/json
ARAMEX_PRODUCTION_URL=https://ws.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc/json
ARAMEX_API_TIMEOUT=60

# Billing
ARAMEX_MONTHLY_FEE=100.00
ARAMEX_PER_SHIPMENT_FEE=5.00
ARAMEX_FREE_QUOTA=10
ARAMEX_CURRENCY=SAR

# SMS (Optional)
ARAMEX_SMS_ENABLED=true
ARAMEX_SMS_PROVIDER=twilio
ARAMEX_SMS_FROM=Aramex

# Twilio Configuration (if using Twilio)
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM=your_twilio_number

# Nexmo Configuration (if using Nexmo)
NEXMO_KEY=your_nexmo_key
NEXMO_SECRET=your_nexmo_secret
NEXMO_FROM=your_nexmo_from

# Webhook
ARAMEX_WEBHOOK_SECRET=your-webhook-secret

# Queue
ARAMEX_QUEUE_CONNECTION=default
ARAMEX_QUEUE_NAME=aramex
```

## 🗄️ الخطوة 4: نشر وتشغيل Migrations (Publish and Run Migrations)

```bash
# نشر Migrations
php artisan vendor:publish --tag=aramex-migrations

# تشغيل Migrations
php artisan migrate
```

**الجداول التي سيتم إنشاؤها:**
- `merchant_aramex_accounts` - حسابات التجار مع Aramex
- `aramex_shipments` - سجل جميع الشحنات
- `merchant_billings` - الفواتير الشهرية
- `merchant_transactions` - المعاملات المالية
- `webhook_logs` - سجلات Webhooks

**للتحقق من نجاح Migrations:**
```bash
php artisan migrate:status
```

## 🎨 الخطوة 5: نشر Views (اختياري) (Publish Views - Optional)

إذا كنت تريد تخصيص Dashboard:

```bash
php artisan vendor:publish --tag=aramex-views
```

سيتم نسخ Views إلى `resources/views/vendor/aramex/` حيث يمكنك تعديلها.

## ⚡ الخطوة 6: إعداد Queue Worker (Setup Queue Worker)

**مهم جداً:** يجب تشغيل Queue Worker لمعالجة إنشاء الشحنات وإرسال SMS.

Make sure your queue worker is running:

```bash
php artisan queue:work --queue=aramex
```

Or add to supervisor:

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

### للتطوير (Development)

```bash
php artisan queue:work --queue=aramex
```

### للإنتاج (Production) - استخدام Supervisor

أنشئ ملف `/etc/supervisor/conf.d/aramex-queue.conf`:

```ini
[program:aramex-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/v1/urbill/artisan queue:work --queue=aramex --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/v1/urbill/storage/logs/aramex-queue.log
stopwaitsecs=3600
```

ثم:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start aramex-queue:*
```

## 📅 الخطوة 7: إعداد الأوامر المجدولة (Setup Scheduled Commands)

أضف إلى `app/Console/Kernel.php`:

```php
use Illuminate\Console\Scheduling\Schedule;

protected function schedule(Schedule $schedule)
{
    // مزامنة حالة الشحنات يومياً في الساعة 2 صباحاً
    $schedule->command('aramex:sync-status')
        ->dailyAt('02:00')
        ->withoutOverlapping();
    
    // إنشاء الفواتير الشهرية في أول كل شهر الساعة 12 منتصف الليل
    $schedule->command('aramex:monthly-billing')
        ->monthlyOn(1, '00:00')
        ->withoutOverlapping();
}
```

**ملاحظة:** تأكد من إعداد Cron Job:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## ✅ الخطوة 8: التحقق من التثبيت (Verify Installation)

### التحقق من Routes

```bash
php artisan route:list | grep aramex
```

يجب أن ترى Routes مثل:
- `POST api/aramex/account/connect`
- `POST api/aramex/shipments`
- `GET api/aramex/shipments/track/{trackingNumber}`
- وغيرها...

### التحقق من Commands

```bash
php artisan list | grep aramex
```

يجب أن ترى:
- `aramex:sync-status`
- `aramex:monthly-billing`

### التحقق من Config

```bash
php artisan config:show aramex
```

### التحقق من Database

```bash
php artisan migrate:status
```

يجب أن تكون جميع Migrations تم تشغيلها.

## 🚀 الخطوات التالية (Next Steps)

### 1. إنشاء أول حساب تاجر

#### عبر Model:
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

#### عبر API:
```bash
curl -X POST http://your-domain.com/api/aramex/account/connect \
  -H "X-API-KEY: temporary-key" \
  -H "Content-Type: application/json" \
  -d '{...}'
```

### 2. اختبار الاتصال

```bash
# اختبار الحصول على الحساب
curl -X GET http://your-domain.com/api/aramex/account \
  -H "X-API-KEY: your-api-key"
```

### 3. إنشاء أول شحنة

راجع [API_EXAMPLES.md](API_EXAMPLES.md) أو [USAGE.md](USAGE.md) لأمثلة كاملة.

### 4. الوصول للوحة التحكم

```
http://your-domain.com/aramex/dashboard?merchant_id=merchant_1&api_key=your-api-key
```

## 📚 المزيد من التوثيق

- **[README.md](README.md)** - نظرة عامة واستخدام أساسي
- **[API_EXAMPLES.md](API_EXAMPLES.md)** - أمثلة كاملة لجميع API endpoints
- **[USAGE.md](USAGE.md)** - دليل الاستخدام المتقدم
- **[QUICK_START.md](QUICK_START.md)** - البدء السريع في 5 دقائق

## ⚠️ نصائح مهمة

1. **ابدأ بـ Sandbox:** استخدم `environment: 'sandbox'` للاختبار
2. **احفظ API Keys:** احفظ API Key لكل تاجر في مكان آمن
3. **راقب Queue:** تأكد من أن Queue Worker يعمل بشكل مستمر
4. **راجع Logs:** راجع `storage/logs/laravel.log` للأخطاء
5. **اختبر Webhooks:** اختبر Webhooks في Sandbox أولاً

---

**تم التثبيت بنجاح! 🎉**

