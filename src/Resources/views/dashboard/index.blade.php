<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم Aramex</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-blue-600 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold">Aramex Integration</h1>
                    </div>
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <a href="#account" class="hover:text-blue-200">الحساب</a>
                        <a href="#shipments" class="hover:text-blue-200">الشحنات</a>
                        <a href="#billing" class="hover:text-blue-200">الفواتير</a>
                        <a href="#rate" class="hover:text-blue-200">حاسبة الأسعار</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Account Section -->
            <div id="account" class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4">ربط حساب Aramex</h2>
                <form x-data="accountForm()" @submit.prevent="connectAccount" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">اسم المستخدم</label>
                            <input type="text" x-model="form.aramex_username" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">كلمة المرور</label>
                            <input type="password" x-model="form.aramex_password" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">رقم الحساب</label>
                            <input type="text" x-model="form.account_number" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">PIN</label>
                            <input type="text" x-model="form.account_pin" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Entity</label>
                            <input type="text" x-model="form.entity" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">رمز الدولة</label>
                            <input type="text" x-model="form.country_code" maxlength="2" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">المدينة</label>
                            <input type="text" x-model="form.city" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">البيئة</label>
                            <select x-model="form.environment" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="sandbox">Sandbox</option>
                                <option value="production">Production</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" :disabled="loading"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50">
                        <span x-show="!loading">ربط الحساب</span>
                        <span x-show="loading">جاري الربط...</span>
                    </button>
                </form>
            </div>

            <!-- Create Shipment Section -->
            <div id="shipments" class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4">إنشاء شحنة</h2>
                <form x-data="shipmentForm()" @submit.prevent="createShipment" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">المرجع</label>
                            <input type="text" x-model="form.reference"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">اسم المنتج</label>
                            <input type="text" x-model="form.product_name" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">الوزن (كجم)</label>
                            <input type="number" x-model.number="form.weight" step="0.1" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">مبلغ COD (اختياري)</label>
                            <input type="number" x-model.number="form.cod_amount" step="0.01"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold mt-4">معلومات المستلم</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">الاسم</label>
                            <input type="text" x-model="form.receiver.name" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">الهاتف</label>
                            <input type="tel" x-model="form.receiver.phone" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">البريد الإلكتروني</label>
                            <input type="email" x-model="form.receiver.email" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">المدينة</label>
                            <input type="text" x-model="form.receiver.address.city" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">العنوان</label>
                            <input type="text" x-model="form.receiver.address.line1" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">الرمز البريدي</label>
                            <input type="text" x-model="form.receiver.address.postal_code" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">رمز الدولة</label>
                            <input type="text" x-model="form.receiver.address.country_code" maxlength="2" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>
                    <button type="submit" :disabled="loading"
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:opacity-50">
                        <span x-show="!loading">إنشاء الشحنة</span>
                        <span x-show="loading">جاري الإنشاء...</span>
                    </button>
                </form>
            </div>

            <!-- Rate Calculator Section -->
            <div id="rate" class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4">حاسبة الأسعار</h2>
                <form x-data="rateForm()" @submit.prevent="calculateRate" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">الوزن (كجم)</label>
                            <input type="number" x-model.number="form.weight" step="0.1" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">من المدينة</label>
                            <input type="text" x-model="form.origin.city" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">إلى المدينة</label>
                            <input type="text" x-model="form.destination.city" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>
                    <button type="submit" :disabled="loading"
                        class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 disabled:opacity-50">
                        حساب السعر
                    </button>
                    <div x-show="rate" class="mt-4 p-4 bg-green-50 rounded">
                        <p class="text-lg font-semibold">السعر: <span x-text="rate"></span></p>
                    </div>
                </form>
            </div>

            <!-- Billing Section -->
            <div id="billing" class="bg-white shadow rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">الفواتير والمعاملات</h2>
                <div class="space-y-4">
                    <div class="p-4 bg-blue-50 rounded">
                        <p class="font-semibold">الحصة المجانية المتبقية: <span id="free-quota">-</span></p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التاريخ</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">النوع</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المبلغ</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                                </tr>
                            </thead>
                            <tbody id="transactions-table" class="bg-white divide-y divide-gray-200">
                                <!-- Transactions will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function accountForm() {
            return {
                loading: false,
                form: {
                    merchant_id: '{{ $merchantId ?? "1" }}',
                    aramex_username: '',
                    aramex_password: '',
                    account_number: '',
                    account_pin: '',
                    entity: '',
                    country_code: '',
                    city: '',
                    environment: 'sandbox'
                },
                async connectAccount() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/aramex/account/connect', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-API-KEY': '{{ $apiKey ?? "" }}'
                            },
                            body: JSON.stringify(this.form)
                        });
                        const data = await response.json();
                        if (data.success) {
                            alert('تم ربط الحساب بنجاح!');
                        } else {
                            alert('خطأ: ' + data.message);
                        }
                    } catch (error) {
                        alert('حدث خطأ: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }

        function shipmentForm() {
            return {
                loading: false,
                form: {
                    reference: '',
                    product_name: '',
                    weight: 1,
                    cod_amount: 0,
                    receiver: {
                        name: '',
                        phone: '',
                        email: '',
                        address: {
                            line1: '',
                            city: '',
                            postal_code: '',
                            country_code: ''
                        }
                    }
                },
                async createShipment() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/aramex/shipments', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-API-KEY': '{{ $apiKey ?? "" }}'
                            },
                            body: JSON.stringify(this.form)
                        });
                        const data = await response.json();
                        if (data.success) {
                            alert('تم إنشاء الشحنة بنجاح! رقم التتبع: ' + (data.data?.shipment_number || 'N/A'));
                        } else {
                            alert('خطأ: ' + data.message);
                        }
                    } catch (error) {
                        alert('حدث خطأ: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }

        function rateForm() {
            return {
                loading: false,
                rate: null,
                form: {
                    weight: 1,
                    origin: { city: '', country_code: 'SA' },
                    destination: { city: '', country_code: 'SA' }
                },
                async calculateRate() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/aramex/rate/calculate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-API-KEY': '{{ $apiKey ?? "" }}'
                            },
                            body: JSON.stringify(this.form)
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.rate = data.data.total_amount + ' ' + data.data.currency;
                        } else {
                            alert('خطأ: ' + data.message);
                        }
                    } catch (error) {
                        alert('حدث خطأ: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>

