# تقرير الفحص الفني لحالة المشروع الحالية (`PROJECT_CURRENT_STATUS.md`)

تاريخ الفحص: 3 أغسطس 2026

---

## 1. معلومات البيئة الحالية (Current Environment)
* **اسم التطبيق**: `Laravel` (معرّف في `.env` بـ `APP_NAME=Laravel`)
* **البيئة (`APP_ENV`)**: `local`
* **وضع التطوير (`APP_DEBUG`)**: `true`
* **مسار المشروع**: `c:\laragon\www\costs`
* **مفتاح التطبيق (`APP_KEY`)**: موجود ومولّد بنجاح (`base64:r84wvpwZIhOredT4F8EruLx9PnY1D+Dwz7aClpxrhss=`).
* **المنطقة الزمنية (`timezone`)**: `UTC` (معرّفة في `config/app.php`).
* **حالة ملف `.env`**: موجود ويحتوي على الإعدادات الافتراضية لهيكل Laravel.

---

## 2. إصدار Laravel وPHP
* **إصدار Laravel المستخدم فعلياً**: `Laravel Framework 13.23.0`
* **إصدار PHP المستخدم على الجهاز**: `PHP 8.4.16` (CLI ZTS Visual C++ 2022 x64)
* **إصدار PHP المطلوب في `composer.json`**: `^8.3`
* **نتيجة التوافق**: بيئة PHP 8.4 متوافقة تماماً مع متطلبات المشروع `^8.3`.

---

## 3. حالة قاعدة البيانات (Database Status)
* **الإعداد الحالي في `.env`**: `DB_CONNECTION=sqlite`
* **قاعدة البيانات الحالية المستخدمة**: SQLite عبر الملف `database/database.sqlite`.
* **إعدادات MySQL في `.env`**: معطلة كتعليق (`# DB_HOST=127.0.0.1`, `# DB_PORT=3306`, `# DB_DATABASE=laravel`, ...).
* **فحص خادم MySQL المحلي**:
  * خادم MySQL يعمل محلياً على المنفذ `3306` بشبكة `127.0.0.1`.
  * امتداد `pdo_mysql` مفعل ويعمل في PHP.
  * لم يتم إنشاء قاعدة بيانات مخصصة باسم `costs` في MySQL بعد، ولم يتم تحويل `.env` إليها بعد.

---

## 4. قائمة الحزم الحالية (Installed Packages)
### أ. الحزم في `composer.json`:
* **المطلوبة (`require`)**:
  * `php`: `^8.3`
  * `laravel/framework`: `^13.8`
  * `laravel/tinker`: `^3.0`
* **حزم التطوير (`require-dev`)**:
  * `fakerphp/faker`: `^1.23`
  * `laravel/pail`: `^1.2.5`
  * `laravel/pao`: `^1.0.6`
  * `laravel/pint`: `^1.27`
  * `mockery/mockery`: `^1.6`
  * `nunomaduro/collision`: `^8.6`
  * `phpunit/phpunit`: `^12.5.12`
* **حالة `mcamara/laravel-localization`**: غير مثبتة بعد.

### ب. الحزم في `package.json`:
* `@tailwindcss/vite`: `^4.0.0`
* `concurrently`: `^9.0.1`
* `laravel-vite-plugin`: `^3.1`
* `tailwindcss`: `^4.0.0`
* `vite`: `^8.0.0`

---

## 5. حالة الواجهة الأمامية (Frontend Status)
* **أداة البناء**: `Vite` (عبر ملف `vite.config.js`).
* **إعدادات CSS**: `Tailwind CSS v4` معرف داخل `resources/css/app.css` بـ `@import 'tailwindcss';`.
* **حالة Bootstrap**: غير مثبت وغير موجود نهائياً.
* **حالة jQuery / AJAX**: غير مثبتين في `package.json`.
* **الملفات الأمامية**:
  * `resources/views/welcome.blade.php` (الصفحة الافتراضية لـ Laravel).
  * `resources/js/app.js` (ملف فارغ).
  * `resources/css/app.css` (ملف استيراد Tailwind الأساسي).

---

## 6. حالة اللغات (Localization / Languages)
* **اللغة الافتراضية الحالية (`APP_LOCALE`)**: `en` (اللغة الإنجليزية).
* **لغة الاسترجاع الاحتياطية (`APP_FALLBACK_LOCALE`)**: `en`.
* **دعم العربية / RTL**: غير مهيأ حالياً، ولا توجد ملفات ترجمة عربية في مجلد `lang/`.

---

## 7. حالة Authentication (تسجيل الدخول والأذونات)
* **نظام تسجيل الدخول**: غير موجود نهائياً (لا يوجد Breeze / Jetstream / Fortify / Custom Auth).
* **نماذج المستخدمين**: يوجد نموذج افتراضي واحد `app/Models/User.php`.
* **جدول المستخدمين**: جدول `users` الافتراضي موجود ضمن ملفات الترحيل الهيكلية الأساسية `0001_01_01_000000_create_users_table.php`.

---

## 8. حالة Migrations والمسارات (Migrations & Routes)
### أ. التهجيرات (Migrations):
* تم تنفيذ التهجيرات الافتراضية لـ Laravel على SQLite:
  1. `0001_01_01_000000_create_users_table` (Ran - Batch 1)
  2. `0001_01_01_000001_create_cache_table` (Ran - Batch 1)
  3. `0001_01_01_000002_create_jobs_table` (Ran - Batch 1)

### ب. المسارات (Routes):
* المسارات المعرفة في `routes/web.php`:
  * `GET /` -> يعرض `welcome`
* المسارات النظامية:
  * `GET /up` (Health Check)
  * `GET|PUT storage/{path}`

---

## 9. الملفات أو الأكواد الموجودة مسبقاً (Existing Code)
* لا يوجد أي كود برمجي أو منطق مخصص للورشة، أعمال CNC، المقاولات، اللافتات، المبيعات، أو الحسابات.
* الكود الموجود هو الهيكل الافتراضي الخالص المولد من تثبيت مشروع Laravel جديد (Fresh Skeleton Installation).

---

## 10. المشاكل والأخطاء المكتشفة (Discovered Issues)
1. **قاعدة البيانات غير مفعلة على MySQL**: مشروع Laravel حالياً مائل لاستخدام SQLite بدلاً من MySQL المطلوب، وإعدادات MySQL في `.env` ما زالت مقتطعة/معطلة كتعليقات.
2. **اللغة الافتراضية والمنطقة الزمنية**: ضبط اللغة الحالية هو `en` والمنطقة الزمنية `UTC` بينما المطلوب جعل العربية هي اللغة الافتراضية وتجهيز نمط الاتجاه RTL.
3. **غياب Bootstrap وحزمة الترجمة**: المشروع يحتوي على Tailwind CSS فقط، بينما متطلبات المشروع تشترط دعم Bootstrap وحزمة `mcamara/laravel-localization`.

---

## 11. التوصيات اللازمة قبل بدء التنفيذ
1. **قاعدة البيانات**:
   - إنشاء قاعدة بيانات MySQL مخصصة للمشروع (مثل `costs_db` أو `costs`).
   - تعديل `.env` لضبط الاتصال بـ `DB_CONNECTION=mysql` وتحديد اسم قاعدة البيانات والمستخدم وكلمة المرور.
2. **إعداد اللغات والمكان**:
   - إضافة حزمة `mcamara/laravel-localization`.
   - تعديل `.env` و `config/app.php` لضبط `APP_LOCALE=ar` والمنطقة الزمنية المناسبة (مثل `Asia/Riyadh` أو `Africa/Cairo` حسب مكان الورشة/المشروع).
   - إعداد هيكلية ملفات الترجمة العربية والإنجليزية والدعم الكامل لاتجهات الخطوط (`dir="rtl"` و `dir="ltr"`).
3. **الواجهة الأمامية**:
   - تثبيت وتجهيز Bootstrap عبر npm أو CDN لدعم الواجهات الإدارية المطلوبة.
4. **نظام Auth والدورات**:
   - التخطيط لتجهيز نظام تسجل الدخول وإدارة الصلاحيات بعد اعتماد هذه المرحلة.

---

## 12. تأكيد حالة المشروع (Project Status Confirmation)
* **تأكيد صريح**: هذا المشروع هو مشروع **Laravel جديد كلياً (Fresh Starter Project)** ولم يتم تنفيذ أي ميزة تخص ورشة الأثاث أو CNC أو المقاولات أو الحسابات مسبقاً.
