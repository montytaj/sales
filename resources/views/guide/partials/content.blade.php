<!-- Inline Styles for Visual Polish, Subtle Borders & Animations -->
<style>
/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 24px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

@keyframes floatSoft {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-7px); }
}

@keyframes pulseGlow {
    0%, 100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(37, 99, 235, 0); }
}

.anim-fade-up {
    animation: fadeInUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.anim-delay-1 { animation-delay: 0.1s; }
.anim-delay-2 { animation-delay: 0.2s; }
.anim-delay-3 { animation-delay: 0.3s; }
.anim-delay-4 { animation-delay: 0.4s; }

.anim-float {
    animation: floatSoft 5s ease-in-out infinite;
}

.anim-pulse {
    animation: pulseGlow 3s infinite;
}

/* Subtle Primary / Secondary Card Borders & Glass Styling */
.guide-card-styled {
    background: #ffffff;
    border: 1.5px solid rgba(37, 99, 235, 0.16) !important;
    border-radius: 1.25rem !important;
    box-shadow: 0 4px 18px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(37, 99, 235, 0.03) !important;
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative;
    overflow: hidden;
}

.dark .guide-card-styled {
    background: #0f172a !important;
    border: 1.5px solid rgba(59, 130, 246, 0.2) !important;
    box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.4) !important;
}

.guide-card-styled:hover {
    transform: translateY(-6px);
    border-color: rgba(37, 99, 235, 0.45) !important;
    box-shadow: 0 20px 35px -8px rgba(37, 99, 235, 0.15), 0 8px 16px -4px rgba(15, 23, 42, 0.08) !important;
}

.guide-card-styled::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color, #2563eb), #60a5fa);
    opacity: 0.85;
    transition: height 0.3s ease;
}

.guide-card-styled:hover::before {
    height: 6px;
    opacity: 1;
}

/* Specific Badge Border Accent Overrides */
.guide-card-primary::before { background: linear-gradient(90deg, #2563eb, #60a5fa); }
.guide-card-success::before { background: linear-gradient(90deg, #10b981, #34d399); }
.guide-card-warning::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.guide-card-info::before { background: linear-gradient(90deg, #06b6d4, #38bdf8); }
.guide-card-danger::before { background: linear-gradient(90deg, #ef4444, #f87171); }
.guide-card-purple::before { background: linear-gradient(90deg, #8b5cf6, #c084fc); }

/* Icon Card Glow */
.icon-wrapper-glow {
    transition: transform 0.35s ease, box-shadow 0.35s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.guide-card-styled:hover .icon-wrapper-glow {
    transform: scale(1.12) rotate(3deg);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
}

/* FAQ Card Styling */
.faq-card-styled {
    background: #ffffff;
    border: 1.5px solid rgba(37, 99, 235, 0.12) !important;
    border-radius: 1rem !important;
    transition: all 0.25s ease !important;
}

.dark .faq-card-styled {
    background: #0f172a !important;
    border: 1.5px solid rgba(59, 130, 246, 0.18) !important;
}

.faq-card-styled:hover {
    border-color: rgba(37, 99, 235, 0.4) !important;
    box-shadow: 0 8px 24px -4px rgba(37, 99, 235, 0.12) !important;
}

/* Feature Item Badge Glow */
.feature-item-pill {
    transition: transform 0.2s ease, background-color 0.2s ease;
}
.feature-item-pill:hover {
    transform: translateX(-3px);
}
[dir="ltr"] .feature-item-pill:hover {
    transform: translateX(3px);
}

.rotate-180 {
    transform: rotate(180deg);
}
</style>

<!-- Ambient Hero Section -->
<div class="guide-hero-wrapper text-white mb-5 rounded-4 shadow-lg position-relative overflow-hidden anim-fade-up" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #090d16 100%);">
    <div class="ambient-light-1 anim-float"></div>
    <div class="ambient-light-2 anim-float" style="animation-delay: 2s;"></div>

    <div class="container-fluid max-w-7xl mx-auto position-relative" style="z-index: 2;">
        
        <!-- Header Badge -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fs-7 font-bold d-inline-flex align-items-center gap-1.5 anim-pulse">
                <i class="bi bi-stars"></i>
                <span>{{ app()->getLocale() == 'ar' ? 'الدليل الشامل والتعريفي بالنظام' : 'Comprehensive System Feature Showcase' }}</span>
            </span>

            <span class="text-slate-400 fs-8 d-flex align-items-center gap-1">
                <i class="bi bi-shield-check text-emerald-400 fs-6"></i>
                <span>{{ app()->getLocale() == 'ar' ? 'منظومة إدارية ومحاسبية متكاملة ومؤمنة' : 'Enterprise Secure ERP Suite' }}</span>
            </span>
        </div>

        <!-- Main Title & Subtitle -->
        <div class="row align-items-center g-4 my-2">
            <div class="col-lg-8">
                <h1 class="fw-extrabold text-white tracking-tight display-6 mb-3 lh-sm">
                    {{ app()->getLocale() == 'ar' ? 'شرح وخصائص المنظومة الإدارية والمحاسبية' : 'Complete System Overview & Feature Guide' }}
                </h1>
                <p class="text-slate-300 fs-6 fw-normal mb-4 lh-base" style="max-width: 780px;">
                    {{ app()->getLocale() == 'ar' 
                        ? 'استكشف كافة عناصر وإمكانيات النظام: نقطة البيع السريعة (POS)، المخازن متعددة الوحدات، المشتريات، المنظومة المالية والشيكات، شجرة الحسابات (5 مستويات)، ومصفوفة الصلاحيات مع سجل التدقيق اللحظي.'
                        : 'Explore all system modules & capabilities: Fast Cashier POS, Multi-Unit Inventory, Purchases, Financial Suite, Cheques, 5-Level Chart of Accounts, and RBAC with real-time Audit Logging.' }}
                </p>

                <!-- System Highlights KPI Bar -->
                <div class="row g-2 g-md-3 me-0">
                    <div class="col-6 col-sm-3">
                        <div class="p-2.5 rounded-3 bg-white-10 backdrop-blur border border-white-10 d-flex align-items-center gap-2 hover-lift">
                            <div class="rounded-circle bg-emerald-500-20 text-emerald-400 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-lightning-charge-fill fs-5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-white fs-7">100%</div>
                                <div class="text-slate-400 fs-8">{{ app()->getLocale() == 'ar' ? 'معالجة لحظية' : 'Real-time' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-sm-3">
                        <div class="p-2.5 rounded-3 bg-white-10 backdrop-blur border border-white-10 d-flex align-items-center gap-2 hover-lift">
                            <div class="rounded-circle bg-blue-500-20 text-blue-400 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-diagram-3-fill fs-5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-white fs-7">5 {{ app()->getLocale() == 'ar' ? 'مستويات' : 'Levels' }}</div>
                                <div class="text-slate-400 fs-8">{{ app()->getLocale() == 'ar' ? 'شجرة الحسابات' : 'Chart of Accounts' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-sm-3">
                        <div class="p-2.5 rounded-3 bg-white-10 backdrop-blur border border-white-10 d-flex align-items-center gap-2 hover-lift">
                            <div class="rounded-circle bg-amber-500-20 text-amber-400 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-box-seam-fill fs-5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-white fs-7">{{ app()->getLocale() == 'ar' ? 'تعدد الوحدات' : 'Multi-Unit' }}</div>
                                <div class="text-slate-400 fs-8">{{ app()->getLocale() == 'ar' ? 'تحويل آلي' : 'Auto Convert' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-sm-3">
                        <div class="p-2.5 rounded-3 bg-white-10 backdrop-blur border border-white-10 d-flex align-items-center gap-2 hover-lift">
                            <div class="rounded-circle bg-purple-500-20 text-purple-400 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-shield-lock-fill fs-5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-white fs-7">RBAC</div>
                                <div class="text-slate-400 fs-8">{{ app()->getLocale() == 'ar' ? 'صلاحيات وتدقيق' : 'Audit Trail' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Search Input Box -->
            <div class="col-lg-4">
                <div class="card border-0 rounded-4 p-3.5 shadow-lg" style="background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.18) !important;">
                    <h6 class="font-bold text-white mb-2 fs-6 d-flex align-items-center gap-2">
                        <i class="bi bi-search text-primary"></i>
                        <span>{{ app()->getLocale() == 'ar' ? 'البحث السريع في خصائص النظام' : 'Search System Features' }}</span>
                    </h6>
                    <p class="text-slate-300 fs-8 mb-3">
                        {{ app()->getLocale() == 'ar' ? 'اكتب أي كلمة مثل (كاشير، شيكات، مخازن، وحدات، صلاحيات) للبحث اللحظي.' : 'Type any keyword like (POS, Cheque, Warehouse, Units, Audit) for live search.' }}
                    </p>

                    <div class="position-relative mb-3">
                        <input type="text" 
                               id="guideSearchInput" 
                               class="form-control form-control-lg bg-slate-900 border-slate-700 text-white placeholder-slate-400 rounded-3 fs-7 ps-4 py-2.5" 
                               placeholder="{{ app()->getLocale() == 'ar' ? 'ابحث عن خاصية أو وحدة...' : 'Search feature or module...' }}">
                    </div>

                    <div class="d-flex align-items-center justify-content-between text-slate-400 fs-8">
                        <span>{{ app()->getLocale() == 'ar' ? 'النتائج المطابقة:' : 'Matching items:' }}</span>
                        <span id="matchingCountBadge" class="badge bg-primary text-white font-bold px-2.5 py-1 rounded-pill">6 {{ app()->getLocale() == 'ar' ? 'وحدات رئيسية' : 'Modules' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Category Tabs -->
        <div class="d-flex align-items-center gap-2 overflow-x-auto pb-2 mt-4 pt-3 border-top border-white-10 custom-scrollbar">
            <button class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fs-7 font-medium guide-category-btn active" data-category="all">
                <i class="bi bi-grid-fill me-1"></i> {{ app()->getLocale() == 'ar' ? 'الكل' : 'All Modules' }}
            </button>
            <button class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fs-7 font-medium guide-category-btn" data-category="sales">
                <i class="bi bi-display me-1"></i> {{ app()->getLocale() == 'ar' ? 'المبيعات و POS' : 'POS & Sales' }}
            </button>
            <button class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fs-7 font-medium guide-category-btn" data-category="inventory">
                <i class="bi bi-box-seam me-1"></i> {{ app()->getLocale() == 'ar' ? 'المخازن والوحدات' : 'Inventory & Units' }}
            </button>
            <button class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fs-7 font-medium guide-category-btn" data-category="purchases">
                <i class="bi bi-cart-plus me-1"></i> {{ app()->getLocale() == 'ar' ? 'المشتريات والموردين' : 'Purchases' }}
            </button>
            <button class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fs-7 font-medium guide-category-btn" data-category="finance">
                <i class="bi bi-bank2 me-1"></i> {{ app()->getLocale() == 'ar' ? 'المالية والحسابات' : 'Finance & Accounts' }}
            </button>
            <button class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fs-7 font-medium guide-category-btn" data-category="security">
                <i class="bi bi-shield-lock me-1"></i> {{ app()->getLocale() == 'ar' ? 'الصلاحيات والرقابة' : 'RBAC & Audit' }}
            </button>
            <button class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fs-7 font-medium guide-category-btn" data-category="reports">
                <i class="bi bi-bar-chart-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'التقارير' : 'Reports' }}
            </button>
        </div>
    </div>
</div>

<!-- Main System Modules Showcase Grid -->
<div class="container-fluid max-w-7xl mx-auto px-0 mb-5">
    
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold text-slate-900 dark:text-white mb-1 fs-4">
                {{ app()->getLocale() == 'ar' ? 'الوحدات والقطاعات الرئيسية في المنظومة' : 'Core System Modules & Features' }}
            </h3>
            <p class="text-slate-500 fs-7 mb-0">
                {{ app()->getLocale() == 'ar' ? 'استعراض تفصيلي لجميع المكونات والوظائف التنفيذية والمحاسبية' : 'Detailed breakdown of all functional modules and business processes' }}
            </p>
        </div>
    </div>

    <!-- Modules Cards Container -->
    <div class="row g-4" id="guideModulesContainer">
        @foreach($modules as $index => $module)
            <div class="col-lg-6 guide-module-card-col anim-fade-up anim-delay-{{ ($index % 4) + 1 }}" data-category="{{ $module['category'] }}" data-keywords="{{ strtolower($module['title_ar'] . ' ' . $module['title_en'] . ' ' . implode(' ', $module['features_ar']) . ' ' . implode(' ', $module['features_en'])) }}">
                <div class="guide-card-styled guide-card-{{ $module['badge_color'] }} h-100 p-4 d-flex flex-column">
                    
                    <!-- Top Card Header -->
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-wrapper-glow rounded-3 bg-{{ $module['badge_color'] }}-subtle text-{{ $module['badge_color'] }} p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px;">
                                <i class="bi {{ $module['icon'] }} fs-3"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-slate-900 dark:text-white mb-1 fs-5">
                                    {{ app()->getLocale() == 'ar' ? $module['title_ar'] : $module['title_en'] }}
                                </h5>
                                <span class="badge bg-{{ $module['badge_color'] }}-subtle text-{{ $module['badge_color'] }} border border-{{ $module['badge_color'] }}-subtle rounded-pill font-medium fs-8">
                                    {{ app()->getLocale() == 'ar' ? 'وحدة رئيسية' : 'Core Module' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <p class="text-slate-600 dark:text-slate-300 fs-7 mb-3.5 lh-base">
                        {{ app()->getLocale() == 'ar' ? $module['summary_ar'] : $module['summary_en'] }}
                    </p>

                    <hr class="my-3 text-slate-200 dark:text-slate-800">

                    <!-- Bullet Features List -->
                    <h6 class="font-bold text-slate-800 dark:text-slate-200 fs-7 mb-2.5 d-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle text-{{ $module['badge_color'] }}"></i>
                        <span>{{ app()->getLocale() == 'ar' ? 'أهم الإمكانيات والخصائص:' : 'Key Capabilities:' }}</span>
                    </h6>

                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 flex-grow-1">
                        @php 
                            $featuresList = app()->getLocale() == 'ar' ? $module['features_ar'] : $module['features_en'];
                        @endphp
                        @foreach($featuresList as $feat)
                            <li class="feature-item-pill d-flex align-items-start gap-2 fs-7 text-slate-700 dark:text-slate-300">
                                <i class="bi bi-check-circle-fill text-success fs-7 flex-shrink-0 mt-0.5"></i>
                                <span>{{ $feat }}</span>
                            </li>
                        @endforeach
                    </ul>

                </div>
            </div>
        @endforeach
    </div>

    <!-- No Search Results Fallback State -->
    <div id="noResultsState" class="text-center py-5 d-none">
        <div class="rounded-circle bg-primary-subtle text-primary p-4 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
            <i class="bi bi-search fs-1"></i>
        </div>
        <h5 class="fw-bold text-slate-900 dark:text-white mb-2 fs-5">
            {{ app()->getLocale() == 'ar' ? 'لم يتم العثور على أية نتائج مطابقة للبحث' : 'No matching system features found' }}
        </h5>
        <p class="text-slate-500 fs-7 mb-3">
            {{ app()->getLocale() == 'ar' ? 'جرّب البحث بمصطلحات أخرى مثل (مخازن، كاشير، شيكات، وحدات، صلاحيات)' : 'Try searching with other terms like (POS, Warehouse, Cheques, Units, Audit)' }}
        </p>
        <button id="resetSearchBtn" class="btn btn-primary rounded-3 px-4 fs-7 font-semibold">
            {{ app()->getLocale() == 'ar' ? 'إعادة عرض كافة الخصائص' : 'Show All Features' }}
        </button>
    </div>

</div>

<!-- System Workflows & Data Flow (خارطة دورة العمل المحاسبية والإدارية) -->
<div class="guide-card-styled p-4 p-md-5 mb-5 border-accent-primary anim-fade-up">
    <div class="text-center mb-4 max-w-3xl mx-auto">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fs-7 font-bold mb-2">
            <i class="bi bi-diagram-2 me-1"></i> {{ app()->getLocale() == 'ar' ? 'دورة الحركات المتكاملة' : 'System Workflow Lifecycle' }}
        </span>
        <h3 class="fw-extrabold text-slate-900 dark:text-white fs-3 mb-2">
            {{ app()->getLocale() == 'ar' ? 'كيف تتكامل البيانات والدورة المحاسبية في المنظومة؟' : 'How Data & Accounting Flow Seamlessly' }}
        </h3>
        <p class="text-slate-500 fs-7 mb-0">
            {{ app()->getLocale() == 'ar' 
                ? 'تنتقل البيانات بسلاسة وآلية من مرحلة إدخال البيانات الأساسية والمخازن إلى فواتير الشراء والبيع والتحصيل والتأثير المحاسبي اللحظي.'
                : 'Data flows automatically from master setup and inventory to purchases, cashier sales, payments, and instant journal entries.' }}
        </p>
    </div>

    <!-- 5 Connected Step Cards -->
    <div class="row g-3 g-md-4">
        <!-- Step 1 -->
        <div class="col-md-4 col-lg">
            <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 h-100 text-center hover-lift position-relative">
                <div class="badge bg-primary text-white rounded-circle fs-6 mb-2.5 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">1</div>
                <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1">
                    {{ app()->getLocale() == 'ar' ? 'البيانات والمخازن' : 'Master Data' }}
                </h6>
                <p class="text-slate-500 fs-8 mb-0">
                    {{ app()->getLocale() == 'ar' ? 'تعريف وحدات القياس (كرتونة/حبة)، الفئات، والمستودعات.' : 'Setup units, item categories, and multi-warehouse structure.' }}
                </p>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="col-md-4 col-lg">
            <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 h-100 text-center hover-lift position-relative">
                <div class="badge bg-success text-white rounded-circle fs-6 mb-2.5 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">2</div>
                <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1">
                    {{ app()->getLocale() == 'ar' ? 'المبيعات والمشتريات' : 'Sales & Purchases' }}
                </h6>
                <p class="text-slate-500 fs-8 mb-0">
                    {{ app()->getLocale() == 'ar' ? 'شاشة الكاشير السريعة POS، فواتير الشراء، وعروض الأسعار.' : 'Fast POS cashier, purchase invoices, and price quotes.' }}
                </p>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="col-md-4 col-lg">
            <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 h-100 text-center hover-lift position-relative">
                <div class="badge bg-warning text-white rounded-circle fs-6 mb-2.5 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">3</div>
                <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1">
                    {{ app()->getLocale() == 'ar' ? 'الخزن والسندات' : 'Cashbox & Vouchers' }}
                </h6>
                <p class="text-slate-500 fs-8 mb-0">
                    {{ app()->getLocale() == 'ar' ? 'تحصيل النقدية، ورديات الكاشير، سندات القبض للصرف والشيكات.' : 'Cash shifts, receipt/payment vouchers, and cheque tracking.' }}
                </p>
            </div>
        </div>

        <!-- Step 4 -->
        <div class="col-md-4 col-lg">
            <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 h-100 text-center hover-lift position-relative">
                <div class="badge bg-info text-white rounded-circle fs-6 mb-2.5 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">4</div>
                <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1">
                    {{ app()->getLocale() == 'ar' ? 'شجرة الحسابات 5 مستويات' : '5-Level Accounting' }}
                </h6>
                <p class="text-slate-500 fs-8 mb-0">
                    {{ app()->getLocale() == 'ar' ? 'توليد وتأثير آلي على قيود الأصول، الخصوم، الإيرادات والمصروفات.' : 'Automatic journal postings to assets, liabilities & revenue accounts.' }}
                </p>
            </div>
        </div>

        <!-- Step 5 -->
        <div class="col-md-4 col-lg">
            <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 h-100 text-center hover-lift position-relative">
                <div class="badge bg-purple text-white rounded-circle fs-6 mb-2.5 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">5</div>
                <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1">
                    {{ app()->getLocale() == 'ar' ? 'التقارير وسجل الرقابة' : 'Reports & Audit Trail' }}
                </h6>
                <p class="text-slate-500 fs-8 mb-0">
                    {{ app()->getLocale() == 'ar' ? 'كشوف الحسابات، الأرباح، وسجل تدقيق تفصيلي لكل الإجراءات.' : 'Detailed account statements, profit reports, & complete audit trail.' }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Key System Specifications & Advantages Section (إبراز أهم خصائص ومزايا النظام) -->
<div class="mb-5 anim-fade-up">
    <div class="text-center mb-4">
        <h3 class="fw-extrabold text-slate-900 dark:text-white fs-3 mb-2">
            {{ app()->getLocale() == 'ar' ? 'أبرز مزايا وقوة النظام الإدارية والتكنولوجية' : 'Key Advantages & Technological Strength' }}
        </h3>
        <p class="text-slate-500 fs-7 mb-0">
            {{ app()->getLocale() == 'ar' ? 'لماذا تُعد هذه المنظومة خياراً مثالياً لإدارة المؤسسات والمحلات التجارية؟' : 'Why this ERP system is the ideal choice for business management' }}
        </p>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-md-6 col-lg-3">
            <div class="guide-card-styled p-3.5 border-accent-primary hover-lift h-100">
                <div class="rounded-3 bg-primary-subtle text-primary p-2.5 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-speedometer fs-4"></i>
                </div>
                <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1.5">
                    {{ app()->getLocale() == 'ar' ? 'سرعة فائقة واختصارات' : 'High Speed & Shortcuts' }}
                </h6>
                <p class="text-slate-600 dark:text-slate-400 fs-8 mb-0">
                    {{ app()->getLocale() == 'ar' ? 'شاشة POS مصممة للعمل بسرعة مع اختصارات لوحة المفاتيح والباركود.' : 'POS screen designed for maximum speed with full keyboard shortcuts.' }}
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="guide-card-styled p-3.5 border-accent-success hover-lift h-100">
                <div class="rounded-3 bg-success-subtle text-success p-2.5 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-arrows-expand fs-4"></i>
                </div>
                <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1.5">
                    {{ app()->getLocale() == 'ar' ? 'تعدد الوحدات للصنف' : 'Multi-Unit Mapping' }}
                </h6>
                <p class="text-slate-600 dark:text-slate-400 fs-8 mb-0">
                    {{ app()->getLocale() == 'ar' ? 'دعم كرتونة وطرد وحبة للصنف الواحد مع احتساب آلي للأسعار والكميات.' : 'Sell by carton or piece with automated price & stock conversion.' }}
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="guide-card-styled p-3.5 border-accent-warning hover-lift h-100">
                <div class="rounded-3 bg-warning-subtle text-warning p-2.5 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-printer fs-4"></i>
                </div>
                <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1.5">
                    {{ app()->getLocale() == 'ar' ? 'طباعة حرارية و A4' : 'Thermal & A4 Printing' }}
                </h6>
                <p class="text-slate-600 dark:text-slate-400 fs-8 mb-0">
                    {{ app()->getLocale() == 'ar' ? 'طباعة فورية للفواتير بالسند الحراري (80mm) وقياس A4 بهوية المنشأة.' : 'Instant printing for receipts (80mm thermal & A4 invoice layouts).' }}
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="guide-card-styled p-3.5 border-accent-danger hover-lift h-100">
                <div class="rounded-3 bg-danger-subtle text-danger p-2.5 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-shield-check fs-4"></i>
                </div>
                <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1.5">
                    {{ app()->getLocale() == 'ar' ? 'رقابة وسجل تدقيق' : 'Audit Trail & Security' }}
                </h6>
                <p class="text-slate-600 dark:text-slate-400 fs-8 mb-0">
                    {{ app()->getLocale() == 'ar' ? 'سجل تدقيق كامل لرصد هوية وتوقيت كافة التعديلات والتغييرات بالنظام.' : 'Full audit log recording user IDs, timestamps, and exact operations.' }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Interactive System FAQ Accordion Powered by Alpine.js (Fixed 1-Second Disappearing Bug!) -->
<div x-data="{ openFaq: 1 }" class="guide-card-styled p-4 p-md-5 mb-5 border-accent-info anim-fade-up">
    <div class="mb-4">
        <h4 class="fw-bold text-slate-900 dark:text-white mb-1 fs-4 d-flex align-items-center gap-2">
            <i class="bi bi-patch-question-fill text-primary fs-3"></i>
            <span>{{ app()->getLocale() == 'ar' ? 'الأسئلة الشائعة والأدلة التوضيحية' : 'Frequently Asked Questions' }}</span>
        </h4>
        <p class="text-slate-500 fs-7 mb-0">
            {{ app()->getLocale() == 'ar' ? 'إجابات عن أكثر الاستفسارات حول كيفية عمل واستخدام المنظومة' : 'Answers to common questions about system features and operations' }}
        </p>
    </div>

    <div class="d-flex flex-column gap-3">
        <!-- Q1 -->
        <div class="faq-card-styled overflow-hidden">
            <button type="button" 
                    @click="openFaq = (openFaq === 1 ? null : 1)" 
                    class="w-100 text-start p-3.5 p-md-4 d-flex align-items-center justify-content-between border-0 bg-transparent cursor-pointer font-bold text-slate-800 dark:text-slate-100 fs-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                        <i class="bi bi-question-lg fs-5"></i>
                    </div>
                    <span class="fs-6 font-bold text-slate-900 dark:text-white">
                        {{ app()->getLocale() == 'ar' ? '1. هل يمكن استعراض دليل ومعلومات المنظومة دون الحاجة لتسجيل الدخول؟' : '1. Can anyone access and explore this guide without logging in?' }}
                    </span>
                </div>
                <i class="bi bi-chevron-down fs-6 text-slate-400 transition-transform duration-300 me-2" :class="{ 'rotate-180 text-primary': openFaq === 1 }"></i>
            </button>
            <div x-show="openFaq === 1" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="px-4 pb-4 pt-2 fs-7 text-slate-600 dark:text-slate-300 lh-base border-top border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800-50">
                {{ app()->getLocale() == 'ar' 
                    ? 'نعم، تم تصميم هذه الصفحة خصيصاً لتكون متاحة بشكل عام من خلال رابط صفحة تسجيل الدخول والقائمة الجانبية، مما يسمح لأي مستخدم أو زائر بالتعرف على إمكانيات وخصائص المنظومة دون الحاجة لإدخال بيانات تسجيل الدخول.'
                    : 'Yes! This page is publicly accessible directly via the login page link or sidebar menu, allowing anyone to review the full capability set of the system without prior authentication.' }}
            </div>
        </div>

        <!-- Q2 -->
        <div class="faq-card-styled overflow-hidden">
            <button type="button" 
                    @click="openFaq = (openFaq === 2 ? null : 2)" 
                    class="w-100 text-start p-3.5 p-md-4 d-flex align-items-center justify-content-between border-0 bg-transparent cursor-pointer font-bold text-slate-800 dark:text-slate-100 fs-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                        <i class="bi bi-box-seam fs-5"></i>
                    </div>
                    <span class="fs-6 font-bold text-slate-900 dark:text-white">
                        {{ app()->getLocale() == 'ar' ? '2. كيف يتعامل النظام مع الأصناف ذات الوحدات المتعددة (كرتونة / حبة)؟' : '2. How does the system handle multi-unit inventory (Carton vs Piece)?' }}
                    </span>
                </div>
                <i class="bi bi-chevron-down fs-6 text-slate-400 transition-transform duration-300 me-2" :class="{ 'rotate-180 text-success': openFaq === 2 }"></i>
            </button>
            <div x-show="openFaq === 2" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="px-4 pb-4 pt-2 fs-7 text-slate-600 dark:text-slate-300 lh-base border-top border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800-50">
                {{ app()->getLocale() == 'ar' 
                    ? 'يتيح لك النظام تعريف الصنف الواحد بأكثر من وحدة قياس؛ مثل إدخال الشراء بالكرتونة والبيع بالحبة أو الكرتونة. يقوم النظام بضرب وقسمة معامل التحويل تلقائياً لتحديث رصيد المخزون بدقة متناهية ودون أي تضارب.'
                    : 'The system allows mapping multiple units to a single item (e.g., purchasing in Cartons and selling in Pieces or Cartons). It automatically applies conversion factors to manage inventory balance accurately.' }}
            </div>
        </div>

        <!-- Q3 -->
        <div class="faq-card-styled overflow-hidden">
            <button type="button" 
                    @click="openFaq = (openFaq === 3 ? null : 3)" 
                    class="w-100 text-start p-3.5 p-md-4 d-flex align-items-center justify-content-between border-0 bg-transparent cursor-pointer font-bold text-slate-800 dark:text-slate-100 fs-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                        <i class="bi bi-diagram-3 fs-5"></i>
                    </div>
                    <span class="fs-6 font-bold text-slate-900 dark:text-white">
                        {{ app()->getLocale() == 'ar' ? '3. ما هي شجرة الحسابات المحاسبية بـ 5 مستويات وكيف تعمل؟' : '3. What is the 5-Level Chart of Accounts structure?' }}
                    </span>
                </div>
                <i class="bi bi-chevron-down fs-6 text-slate-400 transition-transform duration-300 me-2" :class="{ 'rotate-180 text-warning': openFaq === 3 }"></i>
            </button>
            <div x-show="openFaq === 3" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="px-4 pb-4 pt-2 fs-7 text-slate-600 dark:text-slate-300 lh-base border-top border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800-50">
                {{ app()->getLocale() == 'ar' 
                    ? 'تُعد شجرة الحسابات الهيكل الأساسي للنظام المحاسبي؛ حيث تتكون من 5 مستويات هرمية تبدأ من الحسابات الرئيسية (الأصول، الخصوم، الملكية، الإيرادات، المصروفات) وتنتهي بالحسابات الفرعية التحليلية. تُولّد كافة عمليات المبيعات والمشتريات والسندات قيوداً محاسبية تلقائية في هذا الدليل.'
                    : 'The Chart of Accounts provides a 5-level hierarchical tree structure covering Assets, Liabilities, Equity, Revenues, and Expenses down to analytical sub-accounts. All sales, purchases, and vouchers post automatic journal entries to this tree.' }}
            </div>
        </div>

        <!-- Q4 -->
        <div class="faq-card-styled overflow-hidden">
            <button type="button" 
                    @click="openFaq = (openFaq === 4 ? null : 4)" 
                    class="w-100 text-start p-3.5 p-md-4 d-flex align-items-center justify-content-between border-0 bg-transparent cursor-pointer font-bold text-slate-800 dark:text-slate-100 fs-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                        <i class="bi bi-shield-check fs-5"></i>
                    </div>
                    <span class="fs-6 font-bold text-slate-900 dark:text-white">
                        {{ app()->getLocale() == 'ar' ? '4. ما الذي يقدمه سجل تتبع الحركات والرقابة (Audit Trail)؟' : '4. What does the Audit Trail log track?' }}
                    </span>
                </div>
                <i class="bi bi-chevron-down fs-6 text-slate-400 transition-transform duration-300 me-2" :class="{ 'rotate-180 text-danger': openFaq === 4 }"></i>
            </button>
            <div x-show="openFaq === 4" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="px-4 pb-4 pt-2 fs-7 text-slate-600 dark:text-slate-300 lh-base border-top border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800-50">
                {{ app()->getLocale() == 'ar' 
                    ? 'يقوم سجل الرقابة بإنشاء تتبع رقمي غير قابل للتعديل لكل عملية تتم بالنظام؛ يسجل هوية المستخدم، عنوان IP، التوقيت بالثانية، ونوع الحركة (إضافة، تعديل، إلغاء، حذف) مع التفاصيل الدقيقة قبل وبعد التغيير لأعلى مستويات الأمن والشفافية.'
                    : 'The Audit Trail maintains an immutable security digital log of all system events—recording user ID, IP address, exact timestamp, and operation type (Create, Update, Cancel, Delete) with before/after state details.' }}
            </div>
        </div>

        <!-- Q5 -->
        <div class="faq-card-styled overflow-hidden">
            <button type="button" 
                    @click="openFaq = (openFaq === 5 ? null : 5)" 
                    class="w-100 text-start p-3.5 p-md-4 d-flex align-items-center justify-content-between border-0 bg-transparent cursor-pointer font-bold text-slate-800 dark:text-slate-100 fs-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                        <i class="bi bi-display fs-5"></i>
                    </div>
                    <span class="fs-6 font-bold text-slate-900 dark:text-white">
                        {{ app()->getLocale() == 'ar' ? '5. كيف تعمل شاشة الكاشير (POS) وهل تدعم اختصارات لوحة المفاتيح والطباعة الحرارية؟' : '5. How does the cashier POS screen work and does it support thermal printing?' }}
                    </span>
                </div>
                <i class="bi bi-chevron-down fs-6 text-slate-400 transition-transform duration-300 me-2" :class="{ 'rotate-180 text-info': openFaq === 5 }"></i>
            </button>
            <div x-show="openFaq === 5" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="px-4 pb-4 pt-2 fs-7 text-slate-600 dark:text-slate-300 lh-base border-top border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800-50">
                {{ app()->getLocale() == 'ar' 
                    ? 'صُممت شاشة الكاشير لسرعة الأداء في المحلات والمؤسسات، حيث تدعم البحث السريع بقارئ الباركود، واستخدام اختصارات سريعة للبيع والتعليق والتحصيل، مع الطباعة المباشرة على الطابعات الحرارية (80mm) وطابعات القياس الرسمي (A4).'
                    : 'The POS screen is built for maximum cashier speed—supporting barcode scanners, quick keyboard shortcuts, transaction holding/resuming, and direct printing for 80mm thermal receipts & official A4 invoices.' }}
            </div>
        </div>
    </div>
</div>

<!-- Call To Action Bottom Banner -->
<div class="card border-0 rounded-4 p-4 p-md-5 text-center text-white position-relative overflow-hidden shadow-lg anim-fade-up" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 60%, #064e3b 100%);">
    <div class="position-relative" style="z-index: 2;">
        <h3 class="fw-extrabold text-white mb-2 fs-2">
            {{ app()->getLocale() == 'ar' ? 'جاهز للبدء واستخدام المنظومة؟' : 'Ready to Experience the ERP System?' }}
        </h3>
        <p class="text-slate-300 fs-6 mb-4 max-w-2xl mx-auto">
            {{ app()->getLocale() == 'ar' 
                ? 'سجّل دخولك الآن للوصول الكامل إلى شاشة الكاشير، لوحة التحكم اللحظية، وإدارة المخازن والمالية.'
                : 'Sign in now to access the cashier POS, real-time analytics dashboard, inventory, and financial modules.' }}
        </p>

        <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-emerald-custom btn-lg rounded-3 px-4 font-bold fs-6 d-inline-flex align-items-center gap-2 hover-lift">
                    <i class="bi bi-speedometer2 fs-5"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'الانتقال للوحة التحكم' : 'Go to Dashboard' }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-emerald-custom btn-lg rounded-3 px-5 font-bold fs-6 d-inline-flex align-items-center gap-2 hover-lift">
                    <i class="bi bi-box-arrow-in-right fs-5"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'تسجيل الدخول للنظام' : 'Sign In to System' }}</span>
                </a>
            @endauth
        </div>
    </div>
</div>

<!-- Client-side Interactive Filter & Search Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('guideSearchInput');
    const categoryBtns = document.querySelectorAll('.guide-category-btn');
    const moduleCols = document.querySelectorAll('.guide-module-card-col');
    const matchingCountBadge = document.getElementById('matchingCountBadge');
    const noResultsState = document.getElementById('noResultsState');
    const resetSearchBtn = document.getElementById('resetSearchBtn');

    let activeCategory = 'all';

    function filterModules() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        let visibleCount = 0;

        moduleCols.forEach(col => {
            const moduleCategory = col.getAttribute('data-category');
            const keywords = col.getAttribute('data-keywords') || '';

            const matchesCategory = (activeCategory === 'all' || moduleCategory === activeCategory);
            const matchesSearch = (!query || keywords.includes(query));

            if (matchesCategory && matchesSearch) {
                col.classList.remove('d-none');
                visibleCount++;
            } else {
                col.classList.add('d-none');
            }
        });

        // Update count badge
        if (matchingCountBadge) {
            const isAr = '{{ app()->getLocale() }}' === 'ar';
            matchingCountBadge.textContent = `${visibleCount} ${isAr ? 'وحدات رئيسية' : 'Modules'}`;
        }

        // Handle empty state
        if (noResultsState) {
            if (visibleCount === 0) {
                noResultsState.classList.remove('d-none');
            } else {
                noResultsState.classList.add('d-none');
            }
        }
    }

    // Category button click listeners
    categoryBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            categoryBtns.forEach(b => {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-light');
            });
            this.classList.remove('btn-outline-light');
            this.classList.add('btn-primary', 'active');

            activeCategory = this.getAttribute('data-category');
            filterModules();
        });
    });

    // Real-time input search
    if (searchInput) {
        searchInput.addEventListener('input', filterModules);
    }

    // Reset button handler
    if (resetSearchBtn) {
        resetSearchBtn.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            activeCategory = 'all';

            categoryBtns.forEach((b, idx) => {
                if (idx === 0) {
                    b.classList.remove('btn-outline-light');
                    b.classList.add('btn-primary', 'active');
                } else {
                    b.classList.remove('btn-primary', 'active');
                    b.classList.add('btn-outline-light');
                }
            });

            filterModules();
        });
    }
});
</script>
