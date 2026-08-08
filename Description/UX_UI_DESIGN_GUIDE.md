# دليل نظام التصميم وتجربة المستخدم (`UX_UI_DESIGN_GUIDE.md`)
# Workshop ERP - Unified Design System & UX Standards

---

## 1. نظرة عامة والهدف (Overview & Vision)
يهدف دليل التصميم هذا إلى توفير مرجع قياسي وموحد لكافة الواجهات والمكونات البصرية في منصة **Workshop ERP**. نضمن من خلاله تحقيق تجربة استخدام احترافية، سلسة، متواؤمة كلياً مع اللغة العربية (RTL) والإنجليزية (LTR)، ومتجاوبة 100% مع الأجهزة المختلفة.

---

## 2. نظام الخطوط والطباعة (Typography System)
* **الخط المعتمد**: **Cairo** (من Google Fonts) لجميع الواجهات والنصوص باللغتين العربية والإنجليزية.
* **الأوزان المستخدمة**:
  - `Light (300)`: للنصوص المساعدة الفرعية جداً.
  - `Regular (400)`: للنصوص الرئيسية وجمل المحتوى.
  - `Medium (500)`: لروابط التنقل وحقول النماذج والعناوين الصغيرة.
  - `SemiBold (600)`: لأزرار العمليات، العناوين الفرعية، وشارات الحالات.
  - `Bold (700)`: لعناوين الصفحات، الكروت الرئيسية، والأرقام المالية.
  - `ExtraBold (800)`: لأرقام الإحصائيات (KPIs) والعناوين البارزة.

---

## 3. نظام الألوان المتكامل (Color Palette & Tokens)

جميع الألوان مجهزة كـ CSS Variables مركزية في `resources/css/app.css`:

```css
:root {
    /* Primary Colors */
    --color-primary: #2563eb;
    --color-primary-hover: #1d4ed8;
    --color-primary-light: #eff6ff;

    /* Backgrounds & Surfaces */
    --bg-body: #f8fafc;
    --bg-surface: #ffffff;
    --bg-sidebar: #0f172a;
    --bg-muted: #f1f5f9;

    /* Status Colors */
    --color-success: #10b981;
    --color-warning: #f59e0b;
    --color-danger: #ef4444;
    --color-info: #06b6d4;

    /* Borders & Radius */
    --border-color: #e2e8f0;
    --radius-sm: 0.375rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;
    --radius-pill: 9999px;
}
```

---

## 4. المكونات الأساسية وتطبيق Blade Components

### أ. ترويسة الصفحة (`<x-page-header>`)
مكون موحد لجميع الصفحات الداخلية:
```blade
<x-page-header 
    :title="__('customers.customers_list')" 
    :description="__('customers.manage_desc')" 
    :badge="'نشط'">
    <x-slot name="actions">
        <a href="{{ route('customers.create') }}" class="btn btn-primary-custom">
            <i class="bi bi-plus-lg me-1"></i> إضافة عميل
        </a>
    </x-slot>
</x-page-header>
```

### ب. كرت مؤشرات الأداء (`<x-kpi-card>`)
```blade
<x-kpi-card 
    title="إجمالي المبيعات" 
    value="150,000 ر.س" 
    subtitle="مقارنة بالشهر السابق" 
    icon="bi-graph-up-arrow" 
    color="success" 
    trend="+15%" 
    :trendUp="true" 
    :url="route('sales.index')" />
```

### ج. شارات الحالات الموحدة (`<x-status-badge>`)
```blade
<x-status-badge status="in_progress" />
<x-status-badge status="completed" />
<x-status-badge status="paid" />
<x-status-badge status="unpaid" />
```

### د. شريط الفلترة والبحث (`<x-filter-bar>`)
```blade
<x-filter-bar 
    :action="route('invoices.index')" 
    searchPlaceholder="بحث برقم الفاتورة أو العميل..." 
    :statuses="['paid' => 'مسدد', 'unpaid' => 'غير مسدد']" 
    :showDateFilter="true" />
```

### هـ. الحالات الفارغة (`<x-empty-state>`)
```blade
<x-empty-state 
    icon="bi-inbox" 
    title="لا توجد بيانات مسجلة" 
    description="قم بإضافة عنصر جديد للبدء في عرض البيانات هنا." 
    :actionUrl="route('items.create')" 
    actionLabel="إضافة عنصر جديد" />
```

---

## 5. قواعد الجداول والـ DataTables الاحترافية
1. **الهيكل والمظهر**: استخدام `table-hover` مع `align-middle` وحواف ناعمة.
2. **محاذاة البيانات**:
   - النصوص والعناوين: محاذاة للجهة الرئيسية (يمين في RTL، يسار في LTR).
   - الأرقام والعملات والتواريخ: اتجاه LTR مع تنسيق واضح ومحاذاة يمين/وسط.
3. **قائمة الإجراءات**: تجميع الإجراءات في زر خيارات منسدل (`dropdown`) لمنع ازدحام الصف.

---

## 6. قواعد النماذج والإدخال (Form Design Rules)
- وضع إشارة `*` باللون الأحمر للحقول الإلزامية.
- إظهار رسائل الخطأ تحت الحقل مباشرة باستخدام `invalid-feedback` أو `<x-input-error>`.
- تعطيل زر الحفظ وإظهار مؤشر التحميل (Spinner) فور النقر لمنع الإرسال المكرر.
- تجميع الحقول الكثيرة في أقسام (`Sections`) أو تبويبات (`Tabs`).

---

## 7. قواعد RTL / LTR والتجاوب (Responsive Rules)
- عدم استخدام قيم الاتجاهات الثابتة (`margin-left`, `right: 0px`). الاعتماد الكامل على CSS Logical Properties أو محددات `[dir="rtl"]` و `[dir="ltr"]`.
- تخصيص أزرار كبيرة وعناصر لمس صريحة (حجم لا يقل عن 44px) لتسهيل الاستخدام على شاشات التابليت وفنيي الورشة.

---

## 8. الأداء والتحسين (Performance)
- جميع الأيقونات من مكتبة واحدة (**Bootstrap Icons**).
- لا يتم تحميل أي إطار عمل SPA مغاير (React/Vue/Inertia غير مستخدمة مطلقاً).
- الاعتماد على Server-side pagination لمنع بطء المتصفح في الجداول الكبيرة.
