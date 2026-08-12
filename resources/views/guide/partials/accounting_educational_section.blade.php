<!-- Modern Educational Accounting Cycle & Math Suite Section -->
<div x-data="{
    // COGS Calculator State
    cogsBeg: 20000,
    cogsPurchases: 80000,
    cogsReturns: 5000,
    cogsEnd: 30000,
    get cogsTotal() {
        return Math.max(0, (parseFloat(this.cogsBeg || 0) + parseFloat(this.cogsPurchases || 0)) - parseFloat(this.cogsReturns || 0) - parseFloat(this.cogsEnd || 0));
    },

    // Physical Count Loss State
    countBook: 100000,
    countDamaged: 2000,
    countLost: 1500,
    get countLossTotal() {
        return parseFloat(this.countDamaged || 0) + parseFloat(this.countLost || 0);
    },
    get countValidTotal() {
        return Math.max(0, parseFloat(this.countBook || 0) - this.countLossTotal);
    },

    // Depreciation Calculator State
    depCost: 100000,
    depSalvage: 10000,
    depYears: 5,
    depMethod: 'straight_line',
    depUnitsProduced: 2000,
    depTotalUnits: 10000,
    get depAmount() {
        let cost = parseFloat(this.depCost || 0);
        let salvage = parseFloat(this.depSalvage || 0);
        let years = parseFloat(this.depYears || 1);
        let base = Math.max(0, cost - salvage);

        if (years <= 0) return 0;

        if (this.depMethod === 'straight_line') {
            return (base / years).toFixed(2);
        } else if (this.depMethod === 'declining_balance') {
            let rate = 2 / years;
            return Math.min(cost * rate, base).toFixed(2);
        } else if (this.depMethod === 'units_of_production') {
            let totalU = parseFloat(this.depTotalUnits || 1);
            let prodU = parseFloat(this.depUnitsProduced || 0);
            if (totalU <= 0) return 0;
            return ((base / totalU) * prodU).toFixed(2);
        }
        return 0;
    }
}" class="mb-5">

    <!-- Hero Header Banner for Accounting Cycle -->
    <div class="guide-card-styled p-4 p-md-5 mb-5 border-accent-primary anim-fade-up position-relative overflow-hidden" style="background: radial-gradient(circle at 80% 20%, rgba(37, 99, 235, 0.08) 0%, rgba(15, 23, 42, 0.02) 70%);">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 bg-primary text-white p-3 d-flex align-items-center justify-content-center shadow-lg" style="width: 58px; height: 58px;">
                    <i class="bi bi-diagram-3-fill fs-2"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 font-bold fs-8">
                            {{ app()->getLocale() == 'ar' ? 'المعيار المحاسبي الشامل' : 'Standard Accounting Suite' }}
                        </span>
                        <span class="badge bg-emerald-500-20 text-emerald-600 border border-emerald-500-30 rounded-pill px-3 py-1 font-bold fs-8">
                            <i class="bi bi-heart-fill text-danger me-1"></i> {{ app()->getLocale() == 'ar' ? 'قلب النظام المحاسبي النابض' : 'Heart of Accounting' }}
                        </span>
                    </div>
                    <h2 class="fw-extrabold text-slate-900 dark:text-white fs-3 mb-1 mt-1">
                        {{ app()->getLocale() == 'ar' ? 'الدورة المحاسبية الكاملة والعمليات الحسابية' : 'Full Accounting Cycle & Financial Operations' }}
                    </h2>
                    <p class="text-slate-500 fs-7 mb-0">
                        {{ app()->getLocale() == 'ar' 
                            ? 'رحلة المعاملة المالية من لحظة حدوثها حتى ظهورها في القوائم المالية - دورة مستمرة ومتكررة في كل فترة مالية' 
                            : 'The step-by-step transaction lifecycle from initial recording to final financial statements.' }}
                    </p>
                </div>
            </div>

            <!-- Author & Reference Badge -->
            <div class="p-3 rounded-4 bg-slate-900 text-white border border-slate-700 shadow-sm d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                    <i class="bi bi-person-badge-fill fs-4"></i>
                </div>
                <div>
                    <div class="fs-8 text-slate-400 font-medium">{{ app()->getLocale() == 'ar' ? 'المحتوى والإشراف المحاسبي' : 'Accounting Expertise' }}</div>
                    <div class="fs-7 font-bold text-white">{{ app()->getLocale() == 'ar' ? 'الأستاذ عبد المنعم أبو القاسم الكوني' : 'Prof. Abdul-Monem Al-Koni' }}</div>
                    <div class="fs-8 text-emerald-400 font-semibold">{{ app()->getLocale() == 'ar' ? 'محاسب ومراجع قانوني' : 'Certified Public Accountant (CPA)' }}</div>
                </div>
            </div>
        </div>

        <!-- Section Navigation Pills -->
        <div class="row g-2 pt-3 border-top border-slate-200 dark:border-slate-800">
            <div class="col-6 col-md-3">
                <a href="#section-accounting-cycle" class="text-decoration-none d-block p-2.5 rounded-3 bg-slate-100 dark:bg-slate-800 hover-lift text-center">
                    <span class="font-bold text-primary fs-7 d-block"><i class="bi bi-arrow-repeat me-1"></i> {{ app()->getLocale() == 'ar' ? '1. مراحل الدورة الـ 8' : '1. 8-Stage Cycle' }}</span>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="#section-inventory-cogs" class="text-decoration-none d-block p-2.5 rounded-3 bg-slate-100 dark:bg-slate-800 hover-lift text-center">
                    <span class="font-bold text-emerald-600 fs-7 d-block"><i class="bi bi-boxes me-1"></i> {{ app()->getLocale() == 'ar' ? '2. المخزون و COGS' : '2. Inventory & COGS' }}</span>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="#section-depreciation-methods" class="text-decoration-none d-block p-2.5 rounded-3 bg-slate-100 dark:bg-slate-800 hover-lift text-center">
                    <span class="font-bold text-warning fs-7 d-block"><i class="bi bi-calculator me-1"></i> {{ app()->getLocale() == 'ar' ? '3. الإهلاك وطرق حسابه' : '3. Depreciation' }}</span>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="#section-closing-entries" class="text-decoration-none d-block p-2.5 rounded-3 bg-slate-100 dark:bg-slate-800 hover-lift text-center">
                    <span class="font-bold text-purple fs-7 d-block"><i class="bi bi-lock-fill me-1"></i> {{ app()->getLocale() == 'ar' ? '4. الإقفال وقواعد الحفظ' : '4. Closing Entries' }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ==========================================
         SECTION 1: THE 8-STAGE ACCOUNTING CYCLE
         ========================================== -->
    <div id="section-accounting-cycle" class="guide-card-styled p-4 p-md-5 mb-5 anim-fade-up">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <div>
                <span class="badge bg-primary-subtle text-primary font-bold fs-8 rounded-pill px-3 py-1">
                    <i class="bi bi-diagram-2 me-1"></i> {{ app()->getLocale() == 'ar' ? 'المرحلة التنفيذية' : 'Execution Steps' }}
                </span>
                <h3 class="fw-extrabold text-slate-900 dark:text-white fs-3 mb-1 mt-1">
                    {{ app()->getLocale() == 'ar' ? 'مراحل الدورة المحاسبية الكاملة (8 مراحل)' : 'The 8 Stages of the Accounting Cycle' }}
                </h3>
                <p class="text-slate-500 fs-7 mb-0">
                    {{ app()->getLocale() == 'ar' ? 'التطبيق العملي المتكامل لمراحل القيود والتسويات وحتى القوائم المالية والإقفال' : 'Step-by-step guide from transaction initiation to trial balance & closing.' }}
                </p>
            </div>
        </div>

        <!-- 8 Stage Steps Grid -->
        <div class="row g-3 g-md-4 mb-5">
            <!-- Stage 1 -->
            <div class="col-md-6 col-lg-3">
                <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-primary-subtle h-100 position-relative hover-lift">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-primary text-white rounded-circle fs-6 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">1</span>
                        <i class="bi bi-file-earmark-text-fill fs-3 text-primary"></i>
                    </div>
                    <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1">
                        {{ app()->getLocale() == 'ar' ? '1. حدوث المعاملة المالية' : '1. Transaction Occurrence' }}
                    </h6>
                    <p class="text-slate-600 dark:text-slate-400 fs-8 mb-2">
                        {{ app()->getLocale() == 'ar' ? 'مثل: شراء، دفع، أو بيع، مع استلام وتدقيق الوثيقة المؤيدة للعملية.' : 'Purchase, payment, or sale with supporting source document.' }}
                    </p>
                    <span class="badge bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300 fs-8 font-medium">
                        {{ app()->getLocale() == 'ar' ? 'مرحلة التوثيق' : 'Documentation Stage' }}
                    </span>
                </div>
            </div>

            <!-- Stage 2 -->
            <div class="col-md-6 col-lg-3">
                <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-primary-subtle h-100 position-relative hover-lift">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-primary text-white rounded-circle fs-6 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">2</span>
                        <i class="bi bi-journal-bookmark-fill fs-3 text-primary"></i>
                    </div>
                    <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1">
                        {{ app()->getLocale() == 'ar' ? '2. تسجيل القيود اليومية' : '2. Journal Entries' }}
                    </h6>
                    <p class="text-slate-600 dark:text-slate-400 fs-8 mb-2">
                        {{ app()->getLocale() == 'ar' ? 'تسجيل الحركة في دفتر اليومية المزدوج بشرط القيد المتوازن (المدين = الدائن).' : 'Record double-entry transactions in general journal (Debit = Credit).' }}
                    </p>
                    <span class="badge bg-primary-subtle text-primary fs-8 font-semibold">
                        {{ app()->getLocale() == 'ar' ? 'المدين = الدائن' : 'Debit = Credit' }}
                    </span>
                </div>
            </div>

            <!-- Stage 3 -->
            <div class="col-md-6 col-lg-3">
                <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-primary-subtle h-100 position-relative hover-lift">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-primary text-white rounded-circle fs-6 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">3</span>
                        <i class="bi bi-folder-symlink-fill fs-3 text-primary"></i>
                    </div>
                    <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1">
                        {{ app()->getLocale() == 'ar' ? '3. الترحيل إلى الحسابات' : '3. General Ledger Posting' }}
                    </h6>
                    <p class="text-slate-600 dark:text-slate-400 fs-8 mb-2">
                        {{ app()->getLocale() == 'ar' ? 'ترحيل مبالغ القيود إلى دفتر الأستاذ (الأصول، الخصوم، حقوق الملكية).' : 'Post amounts into individual ledger accounts (Assets, Liabilities, Equity).' }}
                    </p>
                    <span class="badge bg-info-subtle text-info fs-8 font-semibold">
                        {{ app()->getLocale() == 'ar' ? 'دفتر الأستاذ العام' : 'General Ledger' }}
                    </span>
                </div>
            </div>

            <!-- Stage 4 -->
            <div class="col-md-6 col-lg-3">
                <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-primary-subtle h-100 position-relative hover-lift">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-primary text-white rounded-circle fs-6 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">4</span>
                        <i class="bi bi-table fs-3 text-primary"></i>
                    </div>
                    <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1">
                        {{ app()->getLocale() == 'ar' ? '4. إعداد ميزان المراجعة' : '4. Unadjusted Trial Balance' }}
                    </h6>
                    <p class="text-slate-600 dark:text-slate-400 fs-8 mb-2">
                        {{ app()->getLocale() == 'ar' ? 'جمع أرصدة كافة الحسابات والتأكد الحسابي من توازن (إجمالي المدين = الدائن).' : 'Consolidate account balances and verify Total Debit equals Total Credit.' }}
                    </p>
                    <span class="badge bg-warning-subtle text-warning fs-8 font-semibold">
                        {{ app()->getLocale() == 'ar' ? 'فحص توازن الأرصدة' : 'Balance Audit' }}
                    </span>
                </div>
            </div>

            <!-- Stage 5 -->
            <div class="col-md-6 col-lg-3">
                <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-emerald-subtle h-100 position-relative hover-lift">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-emerald-600 text-white rounded-circle fs-6 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">5</span>
                        <i class="bi bi-pencil-square fs-3 text-emerald-500"></i>
                    </div>
                    <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1">
                        {{ app()->getLocale() == 'ar' ? '5. التسويات الجردية' : '5. Adjusting Entries' }}
                    </h6>
                    <p class="text-slate-600 dark:text-slate-400 fs-8 mb-2">
                        {{ app()->getLocale() == 'ar' ? 'تسويات المخزون، الإهلاك، المستحقات والمقدمات وتصحيح الأخطاء.' : 'Adjusting inventory, depreciation, accruals, deferrals, and errors.' }}
                    </p>
                    <span class="badge bg-emerald-500-20 text-emerald-600 fs-8 font-semibold">
                        {{ app()->getLocale() == 'ar' ? 'تسويات نهاية الفترة' : 'Period End Adjustments' }}
                    </span>
                </div>
            </div>

            <!-- Stage 6 -->
            <div class="col-md-6 col-lg-3">
                <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-emerald-subtle h-100 position-relative hover-lift">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-emerald-600 text-white rounded-circle fs-6 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">6</span>
                        <i class="bi bi-scales fs-3 text-emerald-500"></i>
                    </div>
                    <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1">
                        {{ app()->getLocale() == 'ar' ? '6. ميزان المراجعة بعد التسويات' : '6. Adjusted Trial Balance' }}
                    </h6>
                    <p class="text-slate-600 dark:text-slate-400 fs-8 mb-2">
                        {{ app()->getLocale() == 'ar' ? 'التأكد التام من توازن الحسابات بعد التسويات تمهيداً لإعداد القوائم المالية.' : 'Verify account balances equality after adjustments before statement prep.' }}
                    </p>
                    <span class="badge bg-emerald-500-20 text-emerald-600 fs-8 font-semibold">
                        {{ app()->getLocale() == 'ar' ? 'التأكد بعد التسويات' : 'Adjusted Audit' }}
                    </span>
                </div>
            </div>

            <!-- Stage 7 -->
            <div class="col-md-6 col-lg-3">
                <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-purple-subtle h-100 position-relative hover-lift">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-purple text-white rounded-circle fs-6 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">7</span>
                        <i class="bi bi-bar-chart-fill fs-3 text-purple"></i>
                    </div>
                    <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1">
                        {{ app()->getLocale() == 'ar' ? '7. إعداد القوائم المالية' : '7. Financial Statements' }}
                    </h6>
                    <p class="text-slate-600 dark:text-slate-400 fs-8 mb-2">
                        {{ app()->getLocale() == 'ar' ? 'قائمة الدخل، الميزانية العمومية (المركز المالي)، وقائمة التدفقات النقدية.' : 'Produce Income Statement, Balance Sheet, and Cash Flow Statement.' }}
                    </p>
                    <span class="badge bg-purple-subtle text-purple fs-8 font-semibold">
                        {{ app()->getLocale() == 'ar' ? 'التقرير النهائي' : 'Final Reporting' }}
                    </span>
                </div>
            </div>

            <!-- Stage 8 -->
            <div class="col-md-6 col-lg-3">
                <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-purple-subtle h-100 position-relative hover-lift">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-purple text-white rounded-circle fs-6 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">8</span>
                        <i class="bi bi-lock-fill fs-3 text-purple"></i>
                    </div>
                    <h6 class="fw-bold text-slate-900 dark:text-white fs-6 mb-1">
                        {{ app()->getLocale() == 'ar' ? '8. إقفال الحسابات' : '8. Closing Entries' }}
                    </h6>
                    <p class="text-slate-600 dark:text-slate-400 fs-8 mb-2">
                        {{ app()->getLocale() == 'ar' ? 'إقفال حسابات الإيرادات والمصروفات ونقل صافي الأرباح/الخسائر إلى حقوق الملكية.' : 'Close temporary revenue/expense accounts to Retained Earnings.' }}
                    </p>
                    <span class="badge bg-purple-subtle text-purple fs-8 font-semibold">
                        {{ app()->getLocale() == 'ar' ? 'إعادة صفر الحسابات المؤقتة' : 'Zero Temporary Accounts' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Before vs After Accounting Cycle Comparison Card -->
        <div class="card border-0 rounded-4 p-4 bg-slate-900 text-white shadow-lg">
            <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left-right text-primary"></i>
                <span>{{ app()->getLocale() == 'ar' ? 'مقارنة سريعة: المنشأة قبل وبعد تطبيق الدورة المحاسبية' : 'Quick Comparison: Before vs After Accounting Cycle Implementation' }}</span>
            </h5>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead>
                        <tr class="table-active text-slate-300 fs-7">
                            <th style="width: 50%;">{{ app()->getLocale() == 'ar' ? 'قبل الدورة المحاسبية' : 'Before Accounting Cycle' }}</th>
                            <th style="width: 50%;">{{ app()->getLocale() == 'ar' ? 'بعد الدورة المحاسبية' : 'After Accounting Cycle' }}</th>
                        </tr>
                    </thead>
                    <tbody class="fs-7">
                        <tr>
                            <td class="text-rose-400"><i class="bi bi-x-circle me-1"></i> {{ app()->getLocale() == 'ar' ? 'بيانات غير منظمة وعشوائية' : 'Unorganized & random financial records' }}</td>
                            <td class="text-emerald-400 font-semibold"><i class="bi bi-check-circle-fill me-1"></i> {{ app()->getLocale() == 'ar' ? 'بيانات منظمة ودقيقة لحظياً' : 'Organized, accurate & real-time financial data' }}</td>
                        </tr>
                        <tr>
                            <td class="text-rose-400"><i class="bi bi-x-circle me-1"></i> {{ app()->getLocale() == 'ar' ? 'صعوبة في معرفة نتيجة الأعمال (ربح أم خسارة)' : 'Difficulty identifying actual business performance' }}</td>
                            <td class="text-emerald-400 font-semibold"><i class="bi bi-check-circle-fill me-1"></i> {{ app()->getLocale() == 'ar' ? 'معرفة واضحة وشاملة للأرباح والخسائر' : 'Clear & transparent breakdown of Profit & Loss' }}</td>
                        </tr>
                        <tr>
                            <td class="text-rose-400"><i class="bi bi-x-circle me-1"></i> {{ app()->getLocale() == 'ar' ? 'قرارات غير دقيقة وتخمينية' : 'Inaccurate, speculative decision making' }}</td>
                            <td class="text-emerald-400 font-semibold"><i class="bi bi-check-circle-fill me-1"></i> {{ app()->getLocale() == 'ar' ? 'قرارات صحيحة ومبنية على تقارير مالية موثوقة' : 'Sound decisions based on reliable financial reports' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==========================================
         SECTION 2: INVENTORY & PHYSICAL COUNT (COGS)
         ========================================== -->
    <div id="section-inventory-cogs" class="guide-card-styled p-4 p-md-5 mb-5 border-accent-success anim-fade-up">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <div>
                <span class="badge bg-success-subtle text-success font-bold fs-8 rounded-pill px-3 py-1">
                    <i class="bi bi-boxes me-1"></i> {{ app()->getLocale() == 'ar' ? 'معادلة المخزون والجرد' : 'Inventory & COGS Math' }}
                </span>
                <h3 class="fw-extrabold text-slate-900 dark:text-white fs-3 mb-1 mt-1">
                    {{ app()->getLocale() == 'ar' ? 'المخزون والجرد المحاسبي وتكلفة البضاعة المباعة' : 'Inventory Valuation, Physical Count & COGS' }}
                </h3>
                <p class="text-slate-500 fs-7 mb-0">
                    {{ app()->getLocale() == 'ar' 
                        ? 'تعريف المخزون كأصل متداول، احتساب تكلفة البضاعة المباعة، الجرد المستمر مقابل الدوري، وقيد تسوية عجز المخزون' 
                        : 'Learn how inventory behaves, calculate COGS live, compare perpetual vs periodic systems, and handle stock loss entries.' }}
                </p>
            </div>
        </div>

        <!-- COGS Formula & Live Interactive Sandbox Widget -->
        <div class="card border-0 rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, #064e3b 0%, #0f172a 100%); color: #ffffff;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-calculator-fill text-emerald-400"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'حاسبة تكلفة البضاعة المباعة التفاعلية (COGS Calculator)' : 'Interactive COGS Calculator' }}</span>
                </h5>
                <span class="badge bg-emerald-500 text-white font-bold fs-8 px-3 py-1 rounded-pill">
                    {{ app()->getLocale() == 'ar' ? 'معادلة تكلفة المباعة' : 'COGS Formula' }}
                </span>
            </div>

            <!-- Formula Banner Box -->
            <div class="p-3 rounded-3 bg-white-10 backdrop-blur border border-white-20 mb-4 text-center font-bold fs-6">
                <span class="text-emerald-300">{{ app()->getLocale() == 'ar' ? 'تكلفة البضاعة المباعة' : 'COGS' }}</span> = 
                <span class="text-white">{{ app()->getLocale() == 'ar' ? 'مخزون أول المدة' : 'Beginning Inv' }}</span> + 
                <span class="text-white">{{ app()->getLocale() == 'ar' ? 'المشتريات' : 'Purchases' }}</span> - 
                <span class="text-warning">{{ app()->getLocale() == 'ar' ? 'مردودات ومسموحات المشتريات' : 'Purchase Returns' }}</span> - 
                <span class="text-info">{{ app()->getLocale() == 'ar' ? 'مخزون آخر المدة' : 'Ending Inv' }}</span>
            </div>

            <!-- Interactive Inputs Grid -->
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label fs-8 font-semibold text-slate-300">{{ app()->getLocale() == 'ar' ? 'مخزون أول المدة (ر.س/جنيه)' : 'Beginning Inventory' }}</label>
                    <input type="number" x-model="cogsBeg" class="form-control bg-slate-900 text-white border-slate-700 font-bold">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-8 font-semibold text-slate-300">{{ app()->getLocale() == 'ar' ? 'إجمالي المشتريات' : 'Purchases' }}</label>
                    <input type="number" x-model="cogsPurchases" class="form-control bg-slate-900 text-white border-slate-700 font-bold">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-8 font-semibold text-slate-300">{{ app()->getLocale() == 'ar' ? 'مردودات ومسموحات المشتريات' : 'Purchase Returns' }}</label>
                    <input type="number" x-model="cogsReturns" class="form-control bg-slate-900 text-white border-slate-700 font-bold">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-8 font-semibold text-slate-300">{{ app()->getLocale() == 'ar' ? 'مخزون آخر المدة (الجرد)' : 'Ending Inventory' }}</label>
                    <input type="number" x-model="cogsEnd" class="form-control bg-slate-900 text-white border-slate-700 font-bold">
                </div>
            </div>

            <!-- Calculated Live Result Display -->
            <div class="p-3.5 rounded-3 bg-emerald-900-50 border border-emerald-500-40 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <div class="fs-8 text-emerald-300 font-medium">{{ app()->getLocale() == 'ar' ? 'نتيجة الاحتساب المباشرة:' : 'Calculated COGS Result:' }}</div>
                    <div class="fs-7 text-slate-300" x-text="`(${parseFloat(cogsBeg || 0).toLocaleString()} + ${parseFloat(cogsPurchases || 0).toLocaleString()}) - ${parseFloat(cogsReturns || 0).toLocaleString()} - ${parseFloat(cogsEnd || 0).toLocaleString()}`"></div>
                </div>
                <div class="text-end">
                    <span class="fs-8 text-slate-300 d-block">{{ app()->getLocale() == 'ar' ? 'إجمالي تكلفة البضاعة المباعة (COGS)' : 'Total Cost of Goods Sold' }}</span>
                    <span class="fs-3 font-extrabold text-emerald-400" x-text="`${cogsTotal.toLocaleString('ar-SA', {minimumFractionDigits: 2})} ${'{{ app()->getLocale() == 'ar' ? 'جنيه/ر.س' : 'SAR' }}'}`"></span>
                </div>
            </div>
        </div>

        <!-- Systems Comparison: Perpetual vs Periodic & Difference: Inventory vs Expenses -->
        <div class="row g-4 mb-4">
            <!-- Perpetual vs Periodic -->
            <div class="col-lg-6">
                <div class="guide-card-styled p-4 h-100 border-accent-success">
                    <h5 class="fw-bold text-slate-900 dark:text-white mb-3 d-flex align-items-center gap-2 fs-6">
                        <i class="bi bi-arrow-left-right text-success"></i>
                        <span>{{ app()->getLocale() == 'ar' ? 'أنظمة جرد المخزون: الجرد المستمر vs الجرد الدوري' : 'Inventory Systems: Perpetual vs Periodic' }}</span>
                    </h5>
                    
                    <div class="d-flex flex-column gap-3">
                        <div class="p-3 rounded-3 bg-emerald-50 dark:bg-slate-800 border border-emerald-200 dark:border-slate-700">
                            <span class="badge bg-success text-white font-bold mb-1 fs-8">{{ app()->getLocale() == 'ar' ? '1. الجرد المستمر (Perpetual)' : '1. Perpetual System' }}</span>
                            <ul class="list-unstyled mb-0 fs-8 text-slate-700 dark:text-slate-300 mt-2">
                                <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> {{ app()->getLocale() == 'ar' ? 'يتم معرفة رصيد المخزون وتكلفة البضاعة المباعة في أي وقت لحظياً.' : 'Real-time stock balance & COGS availability anytime.' }}</li>
                                <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> {{ app()->getLocale() == 'ar' ? 'يعتمد على البرامج المحاسبية وقارئ الباركود.' : 'Powered by modern accounting software & barcode systems.' }}</li>
                                <li><i class="bi bi-check-circle-fill text-success me-1"></i> {{ app()->getLocale() == 'ar' ? 'مناسب جداً للمنشآت والمؤسسات المتوسطة والكبيرة.' : 'Best fit for medium and enterprise companies.' }}</li>
                            </ul>
                        </div>

                        <div class="p-3 rounded-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            <span class="badge bg-secondary text-white font-bold mb-1 fs-8">{{ app()->getLocale() == 'ar' ? '2. الجرد الدوري (Periodic)' : '2. Periodic System' }}</span>
                            <ul class="list-unstyled mb-0 fs-8 text-slate-700 dark:text-slate-300 mt-2">
                                <li class="mb-1"><i class="bi bi-dash-circle-fill text-slate-400 me-1"></i> {{ app()->getLocale() == 'ar' ? 'لا يتم معرفة المخزون وتكلفة المباعة إلا نهاية الفترة بالجرد الفعلي.' : 'Stock balance & COGS determined only at period end via physical count.' }}</li>
                                <li class="mb-1"><i class="bi bi-dash-circle-fill text-slate-400 me-1"></i> {{ app()->getLocale() == 'ar' ? 'مناسب أكثر للمنشآت والمحلات الصغرى.' : 'Suitable for small local shops.' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Difference: Inventory vs Expenses -->
            <div class="col-lg-6">
                <div class="guide-card-styled p-4 h-100 border-accent-warning">
                    <h5 class="fw-bold text-slate-900 dark:text-white mb-3 d-flex align-items-center gap-2 fs-6">
                        <i class="bi bi-subtract text-warning"></i>
                        <span>{{ app()->getLocale() == 'ar' ? 'الفرق بين المخزون والمصروفات' : 'Difference: Inventory vs Expenses' }}</span>
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle fs-8 text-slate-800 dark:text-slate-200">
                            <thead class="table-light dark:table-dark">
                                <tr>
                                    <th>{{ app()->getLocale() == 'ar' ? 'وجه المقارنة' : 'Aspect' }}</th>
                                    <th>{{ app()->getLocale() == 'ar' ? 'المخزون (Inventory)' : 'Inventory' }}</th>
                                    <th>{{ app()->getLocale() == 'ar' ? 'المصروفات (Expenses)' : 'Expenses' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th class="table-light dark:table-dark">{{ app()->getLocale() == 'ar' ? 'التعريف' : 'Definition' }}</th>
                                    <td>{{ app()->getLocale() == 'ar' ? 'بضائع متاحة للبيع لم يتم بيعها بعد' : 'Goods held for resale' }}</td>
                                    <td>{{ app()->getLocale() == 'ar' ? 'تكاليف تم إنفاقها للحصول على الإيراد' : 'Costs incurred to earn revenue' }}</td>
                                </tr>
                                <tr>
                                    <th class="table-light dark:table-dark">{{ app()->getLocale() == 'ar' ? 'مكان الاعتراف' : 'Recognition' }}</th>
                                    <td><span class="badge bg-primary-subtle text-primary">{{ app()->getLocale() == 'ar' ? 'الميزانية كـ أصل متداول' : 'Balance Sheet (Current Asset)' }}</span></td>
                                    <td><span class="badge bg-warning-subtle text-warning">{{ app()->getLocale() == 'ar' ? 'قائمة الدخل كـ مصروف فترة' : 'Income Statement (Period Expense)' }}</span></td>
                                </tr>
                                <tr>
                                    <th class="table-light dark:table-dark">{{ app()->getLocale() == 'ar' ? 'الأثر على الربح' : 'Profit Impact' }}</th>
                                    <td>{{ app()->getLocale() == 'ar' ? 'لا يؤثر على الربح إلا عند البيع (COGS)' : 'Impacts profit only upon sale' }}</td>
                                    <td>{{ app()->getLocale() == 'ar' ? 'تؤدي لتقليل الربح مباشرة بنفس الفترة' : 'Reduces profit immediately in period' }}</td>
                                </tr>
                                <tr>
                                    <th class="table-light dark:table-dark">{{ app()->getLocale() == 'ar' ? 'أمثلة' : 'Examples' }}</th>
                                    <td>{{ app()->getLocale() == 'ar' ? 'بضاعة في المستودع' : 'Goods in warehouse' }}</td>
                                    <td>{{ app()->getLocale() == 'ar' ? 'إيجار، رواتب، كهرباء' : 'Rent, salaries, electricity' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Physical Count Loss & Shrinkage Journal Entry Interactive Sandbox -->
        <div class="p-4 rounded-4 bg-slate-900 text-white border border-slate-700 shadow-md">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2 fs-6">
                    <i class="bi bi-file-earmark-diff-fill text-rose-400"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'مثال تطبيقي وقيد إثبات نتيجة الجرد (تسوية عجز المخزون وتلفه)' : 'Inventory Loss & Shrinkage Journal Entry Generator' }}</span>
                </h5>
                <span class="badge bg-rose-500 text-white font-bold fs-8 px-3 py-1 rounded-pill">
                    {{ app()->getLocale() == 'ar' ? 'قيد تسوية عجز المخزون' : 'Stock Adjustment Entry' }}
                </span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fs-8 text-slate-300">{{ app()->getLocale() == 'ar' ? 'رصيد المخزون المسجل بالدفاتر' : 'Book Inventory Balance' }}</label>
                    <input type="number" x-model="countBook" class="form-control bg-slate-800 text-white border-slate-700 font-bold">
                </div>
                <div class="col-md-4">
                    <label class="form-label fs-8 text-slate-300">{{ app()->getLocale() == 'ar' ? 'قيمة البضاعة التالفة' : 'Damaged Goods Value' }}</label>
                    <input type="number" x-model="countDamaged" class="form-control bg-slate-800 text-white border-slate-700 font-bold">
                </div>
                <div class="col-md-4">
                    <label class="form-label fs-8 text-slate-300">{{ app()->getLocale() == 'ar' ? 'قيمة البضاعة المفقودة/العجز' : 'Missing/Lost Goods Value' }}</label>
                    <input type="number" x-model="countLost" class="form-control bg-slate-800 text-white border-slate-700 font-bold">
                </div>
            </div>

            <!-- Live Accounting Journal Entry Preview -->
            <div class="p-3.5 rounded-3 bg-slate-950 border border-slate-800">
                <div class="d-flex align-items-center justify-content-between mb-2 fs-8 text-slate-400 border-bottom border-slate-800 pb-2">
                    <span>{{ app()->getLocale() == 'ar' ? 'معاينة قيد اليومية الآلي الناتج:' : 'Generated Journal Entry Preview:' }}</span>
                    <span class="text-rose-400 font-bold" x-text="`{{ app()->getLocale() == 'ar' ? 'إجمالي الخسائر' : 'Total Loss' }}: ${countLossTotal.toLocaleString()} ${'{{ app()->getLocale() == 'ar' ? 'ر.س' : 'SAR' }}'}`"></span>
                </div>

                <div class="d-flex flex-column gap-2 fs-7 font-mono">
                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-slate-900 border-start border-4 border-rose-500">
                        <span class="text-white">
                            <strong class="text-rose-400 font-bold">من حـ/</strong> خسائر مخزون (تالف + مفقود)
                        </span>
                        <span class="badge bg-rose-500-20 text-rose-300 font-bold px-3 py-1" x-text="`${countLossTotal.toLocaleString()} ${'{{ app()->getLocale() == 'ar' ? 'مدين' : 'Debit' }}'}`"></span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-slate-900 border-start border-4 border-emerald-500 me-md-4">
                        <span class="text-white">
                            <strong class="text-emerald-400 font-bold">إلى حـ/</strong> المخزون
                        </span>
                        <span class="badge bg-emerald-500-20 text-emerald-300 font-bold px-3 py-1" x-text="`${countLossTotal.toLocaleString()} ${'{{ app()->getLocale() == 'ar' ? 'دائن' : 'Credit' }}'}`"></span>
                    </div>
                </div>

                <div class="mt-3 text-slate-400 fs-8 d-flex align-items-center justify-content-between border-top border-slate-800 pt-2">
                    <span>{{ app()->getLocale() == 'ar' ? 'رصيد المخزون الصالح للبيع المتبقي:' : 'Remaining Valid Inventory:' }}</span>
                    <span class="text-emerald-400 font-bold fs-7" x-text="`${countValidTotal.toLocaleString()} ${'{{ app()->getLocale() == 'ar' ? 'ر.س/جنيه' : 'SAR' }}'}`"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
         SECTION 3: ASSET DEPRECIATION METHODS
         ========================================== -->
    <div id="section-depreciation-methods" class="guide-card-styled p-4 p-md-5 mb-5 border-accent-warning anim-fade-up">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <div>
                <span class="badge bg-warning-subtle text-warning font-bold fs-8 rounded-pill px-3 py-1">
                    <i class="bi bi-calculator me-1"></i> {{ app()->getLocale() == 'ar' ? 'الإهلاك المحاسبي' : 'Asset Depreciation' }}
                </span>
                <h3 class="fw-extrabold text-slate-900 dark:text-white fs-3 mb-1 mt-1">
                    {{ app()->getLocale() == 'ar' ? 'الإهلاك (Depreciation) وطرق حسابه المحاسبية' : 'Fixed Asset Depreciation & Calculation Methods' }}
                </h3>
                <p class="text-slate-500 fs-7 mb-0">
                    {{ app()->getLocale() == 'ar' 
                        ? 'توزيع تكلفة الأصل الثابت على سنوات عمره الإنتاجي - مع حاسبة حية لجميع الطرق والقيد المحاسبي' 
                        : 'Allocate fixed asset cost over useful life with live multi-method calculation.' }}
                </p>
            </div>

            <!-- Golden Note Badge -->
            <div class="p-2.5 rounded-3 bg-amber-500-10 border border-amber-500-30 text-amber-600 font-bold fs-8 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-amber-500"></i>
                <span>{{ app()->getLocale() == 'ar' ? 'ملاحظة ذهبية: الأراضي لا تهلك؛ لأنها لا تفقد قيمتها بالاستخدام!' : 'Note: Land does NOT depreciate as it retains value!' }}</span>
            </div>
        </div>

        <!-- Interactive Depreciation Calculator Widget -->
        <div class="card border-0 rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); color: #ffffff;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-cpu-fill text-amber-400"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'حاسبة الإهلاك المحاسبي التفاعلية والقيد الآلي' : 'Interactive Depreciation Calculator & Journal Entry' }}</span>
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <select x-model="depMethod" class="form-select form-select-sm bg-slate-900 text-amber-400 border-amber-500-40 font-bold">
                        <option value="straight_line">{{ app()->getLocale() == 'ar' ? 'طريقة القسط الثابت (Straight-Line)' : 'Straight-Line Method' }}</option>
                        <option value="declining_balance">{{ app()->getLocale() == 'ar' ? 'طريقة القسط المتناقص (Declining Balance)' : 'Declining Balance Method' }}</option>
                        <option value="units_of_production">{{ app()->getLocale() == 'ar' ? 'طريقة وحدات الإنتاج (Units of Production)' : 'Units of Production Method' }}</option>
                    </select>
                </div>
            </div>

            <!-- Inputs Row -->
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label fs-8 text-slate-300">{{ app()->getLocale() == 'ar' ? 'تكلفة الأصل الثابت (ر.س)' : 'Asset Purchase Cost' }}</label>
                    <input type="number" x-model="depCost" class="form-control bg-slate-900 text-white border-slate-700 font-bold">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-8 text-slate-300">{{ app()->getLocale() == 'ar' ? 'القيمة التخريدية (الخردة)' : 'Salvage Value' }}</label>
                    <input type="number" x-model="depSalvage" class="form-control bg-slate-900 text-white border-slate-700 font-bold">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-8 text-slate-300">{{ app()->getLocale() == 'ar' ? 'العمر الإنتاجي (سنوات)' : 'Lifespan (Years)' }}</label>
                    <input type="number" x-model="depYears" class="form-control bg-slate-900 text-white border-slate-700 font-bold">
                </div>
                <div class="col-md-3" x-show="depMethod === 'units_of_production'">
                    <label class="form-label fs-8 text-slate-300">{{ app()->getLocale() == 'ar' ? 'الوحدات المنتجة للفترة' : 'Units Produced' }}</label>
                    <input type="number" x-model="depUnitsProduced" class="form-control bg-slate-900 text-white border-slate-700 font-bold">
                </div>
            </div>

            <!-- Calculation Output Box & Journal Entry -->
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="p-3 rounded-3 bg-amber-500-10 border border-amber-500-30 text-center">
                        <div class="fs-8 text-amber-300 font-medium">{{ app()->getLocale() == 'ar' ? 'مبلغ الإهلاك السنوي المحسوب:' : 'Calculated Annual Depreciation:' }}</div>
                        <div class="fs-2 font-extrabold text-amber-400 mt-1" x-text="`${parseFloat(depAmount).toLocaleString('ar-SA')} ${'{{ app()->getLocale() == 'ar' ? 'ر.س/جنيه' : 'SAR' }}'}`"></div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="p-3 rounded-3 bg-slate-950 border border-slate-800">
                        <div class="fs-8 text-slate-400 font-semibold mb-2 border-bottom border-slate-800 pb-1">
                            {{ app()->getLocale() == 'ar' ? 'القيد المحاسبي المولد للإهلاك:' : 'Generated Depreciation Journal Entry:' }}
                        </div>
                        <div class="d-flex flex-column gap-1.5 fs-7 font-mono">
                            <div class="d-flex align-items-center justify-content-between text-emerald-400">
                                <span><strong>من حـ/</strong> مصروف الإهلاك (مدين)</span>
                                <span class="font-bold" x-text="parseFloat(depAmount).toLocaleString()"></span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between text-amber-400 ps-4">
                                <span><strong>إلى حـ/</strong> مجمع الإهلاك (دائن)</span>
                                <span class="font-bold" x-text="parseFloat(depAmount).toLocaleString()"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3 Depreciation Methods Cards Breakdown -->
        <div class="row g-3">
            <div class="col-md-4">
                <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-warning-subtle h-100">
                    <span class="badge bg-warning text-white font-bold mb-2 fs-8">1. {{ app()->getLocale() == 'ar' ? 'طريقة القسط الثابت' : 'Straight-Line Method' }}</span>
                    <h6 class="fw-bold text-slate-900 dark:text-white fs-7 mb-1">
                        {{ app()->getLocale() == 'ar' ? 'تحميل نفس قيمة الإهلاك كل سنة' : 'Equal annual depreciation expense' }}
                    </h6>
                    <p class="text-slate-600 dark:text-slate-400 fs-8 mb-2">
                        {{ app()->getLocale() == 'ar' ? 'الأكثر استخداماً بالمنشآت. المعادلة: (التكلفة - الخردة) / العمر الإنتاجي.' : 'Most common. Formula: (Cost - Salvage) / Useful Life.' }}
                    </p>
                    <div class="p-2 rounded bg-amber-500-10 text-amber-600 dark:text-amber-400 font-bold fs-8 text-center">
                        (100,000 - 10,000) ÷ 5 = 18,000 {{ app()->getLocale() == 'ar' ? 'ر.س/سنة' : 'SAR/yr' }}
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-warning-subtle h-100">
                    <span class="badge bg-warning text-white font-bold mb-2 fs-8">2. {{ app()->getLocale() == 'ar' ? 'طريقة القسط المتناقص' : 'Declining Balance' }}</span>
                    <h6 class="fw-bold text-slate-900 dark:text-white fs-7 mb-1">
                        {{ app()->getLocale() == 'ar' ? 'إهلاك مرتفع البداية ويتخفض تدريجياً' : 'Accelerated early depreciation' }}
                    </h6>
                    <p class="text-slate-600 dark:text-slate-400 fs-8 mb-2">
                        {{ app()->getLocale() == 'ar' ? 'تستخدم للأصول التي تفقد قيمتها بسرعة مثل الأجهزة والمعدات والسيارات.' : 'Ideal for technology, vehicles, & machinery losing value quickly.' }}
                    </p>
                    <div class="p-2 rounded bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-medium fs-8 text-center">
                        {{ app()->getLocale() == 'ar' ? 'تستند لنسبة مضاعفة من القيمة الدفترية' : 'Based on double declining rate of book value' }}
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-3.5 rounded-4 bg-slate-50 dark:bg-slate-800 border border-warning-subtle h-100">
                    <span class="badge bg-warning text-white font-bold mb-2 fs-8">3. {{ app()->getLocale() == 'ar' ? 'طريقة وحدات الإنتاج' : 'Units of Production' }}</span>
                    <h6 class="fw-bold text-slate-900 dark:text-white fs-7 mb-1">
                        {{ app()->getLocale() == 'ar' ? 'يعتمد على حجم الاستخدام أو الوحدات' : 'Based on actual usage/output' }}
                    </h6>
                    <p class="text-slate-600 dark:text-slate-400 fs-8 mb-2">
                        {{ app()->getLocale() == 'ar' ? 'كلما زاد الاستخدام والإنتاج زادت قيمة الإهلاك بالفترة بنفس النسبة.' : 'Depreciation directly proportional to production volume.' }}
                    </p>
                    <div class="p-2 rounded bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-medium fs-8 text-center">
                        {{ app()->getLocale() == 'ar' ? 'معدل الوحدة = القيمة القابلة للإهلاك / الطاقة' : 'Rate/Unit = Depreciable Base / Total Units' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
         SECTION 4: YEAR-END CLOSING ENTRIES & RULES
         ========================================== -->
    <div id="section-closing-entries" class="guide-card-styled p-4 p-md-5 mb-5 border-accent-purple anim-fade-up">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <div>
                <span class="badge bg-purple-subtle text-purple font-bold fs-8 rounded-pill px-3 py-1">
                    <i class="bi bi-lock-fill me-1"></i> {{ app()->getLocale() == 'ar' ? 'إقفال الحسابات وقواعد الحفظ' : 'Closing Entries Matrix & Rules' }}
                </span>
                <h3 class="fw-extrabold text-slate-900 dark:text-white fs-3 mb-1 mt-1">
                    {{ app()->getLocale() == 'ar' ? 'ملخص طرائق الإقفال وقواعد الحفظ التوجيهية' : 'Closing Entries Summary & Easy Memory Rules' }}
                </h3>
                <p class="text-slate-500 fs-7 mb-0">
                    {{ app()->getLocale() == 'ar' ? 'جدول القيود النموذجية لإقفال الحسابات المؤقتة بنهاية السنة المالية وقواعد حفظ المفاهيم المحاسبية' : 'Standard year-end closing entries and quick rule retention cards.' }}
                </p>
            </div>
        </div>

        <!-- Closing Entries Matrix Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle text-slate-800 dark:text-slate-200">
                <thead class="table-dark">
                    <tr class="fs-7">
                        <th style="width: 25%;">{{ app()->getLocale() == 'ar' ? 'الحساب المؤقت' : 'Temporary Account' }}</th>
                        <th style="width: 45%;">{{ app()->getLocale() == 'ar' ? 'طريقة القيد المحاسبي للإقفال' : 'Closing Journal Entry' }}</th>
                        <th style="width: 30%;">{{ app()->getLocale() == 'ar' ? 'الغرض والأثر المحاسبي' : 'Accounting Impact' }}</th>
                    </tr>
                </thead>
                <tbody class="fs-7">
                    <tr>
                        <th class="table-light dark:table-dark font-bold text-emerald-600"><i class="bi bi-graph-up-arrow me-1"></i> {{ app()->getLocale() == 'ar' ? 'الإيرادات (Revenues)' : 'Revenues' }}</th>
                        <td>
                            <div class="font-mono text-slate-900 dark:text-white">
                                <span class="text-emerald-600 font-bold">من حـ/</span> الإيرادات <br>
                                <span class="text-primary font-bold ps-3">إلى حـ/</span> الأرباح والخسائر
                            </div>
                        </td>
                        <td>{{ app()->getLocale() == 'ar' ? 'إقفال الإيرادات وتصفير حسابها بنقل الرصيد للأرباح والخسائر' : 'Zero revenue accounts into Profit & Loss' }}</td>
                    </tr>

                    <tr>
                        <th class="table-light dark:table-dark font-bold text-rose-600"><i class="bi bi-graph-down-arrow me-1"></i> {{ app()->getLocale() == 'ar' ? 'المصروفات (Expenses)' : 'Expenses' }}</th>
                        <td>
                            <div class="font-mono text-slate-900 dark:text-white">
                                <span class="text-primary font-bold">من حـ/</span> الأرباح والخسائر <br>
                                <span class="text-rose-600 font-bold ps-3">إلى حـ/</span> المصروفات
                            </div>
                        </td>
                        <td>{{ app()->getLocale() == 'ar' ? 'إقفال المصروفات وتصفير حسابها مقابل الأرباح والخسائر' : 'Zero expense accounts into Profit & Loss' }}</td>
                    </tr>

                    <tr>
                        <th class="table-light dark:table-dark font-bold text-success"><i class="bi bi-cash-stack me-1"></i> {{ app()->getLocale() == 'ar' ? 'صافي الربح (Net Profit)' : 'Net Profit' }}</th>
                        <td>
                            <div class="font-mono text-slate-900 dark:text-white">
                                <span class="text-primary font-bold">من حـ/</span> الأرباح والخسائر <br>
                                <span class="text-success font-bold ps-3">إلى حـ/</span> رأس المال (أو الأرباح المبقاة)
                            </div>
                        </td>
                        <td>{{ app()->getLocale() == 'ar' ? 'تحويل الفائض والربح النهائي لزيادة حقوق الملكية' : 'Transfer net earnings to Equity / Retained Earnings' }}</td>
                    </tr>

                    <tr>
                        <th class="table-light dark:table-dark font-bold text-danger"><i class="bi bi-dash-circle me-1"></i> {{ app()->getLocale() == 'ar' ? 'صافي الخسارة (Net Loss)' : 'Net Loss' }}</th>
                        <td>
                            <div class="font-mono text-slate-900 dark:text-white">
                                <span class="text-danger font-bold">من حـ/</span> رأس المال (أو الأرباح المبقاة) <br>
                                <span class="text-primary font-bold ps-3">إلى حـ/</span> الأرباح والخسائر
                            </div>
                        </td>
                        <td>{{ app()->getLocale() == 'ar' ? 'خصم وتخفيض رأس المال بقيمة الخسارة المحققة' : 'Reduce Equity by net loss amount' }}</td>
                    </tr>

                    <tr>
                        <th class="table-light dark:table-dark font-bold text-purple"><i class="bi bi-wallet2 me-1"></i> {{ app()->getLocale() == 'ar' ? 'المسحوبات (Drawings)' : 'Owner Drawings' }}</th>
                        <td>
                            <div class="font-mono text-slate-900 dark:text-white">
                                <span class="text-purple font-bold">من حـ/</span> رأس المال <br>
                                <span class="text-slate-600 dark:text-slate-300 font-bold ps-3">إلى حـ/</span> المسحوبات الشخصية
                            </div>
                        </td>
                        <td>{{ app()->getLocale() == 'ar' ? 'إقفال المسحوبات الشخصية للمالك بتخفيض رأس المال' : 'Close owner personal withdrawals against capital' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Quick Retention Rules Cards (قواعد سهلة للحفظ) -->
        <h5 class="fw-bold text-slate-900 dark:text-white mb-3 d-flex align-items-center gap-2 fs-6">
            <i class="bi bi-lightbulb-fill text-amber-500"></i>
            <span>{{ app()->getLocale() == 'ar' ? 'قواعد سهلة وبسيطة للحفظ السريع' : 'Easy Retention Rules & Memory Anchors' }}</span>
        </h5>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3.5 rounded-4 bg-emerald-500-10 border border-emerald-500-30 d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-emerald-500 text-white p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                        <i class="bi bi-box-seam fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-emerald-800 dark:text-emerald-300 fs-7 mb-1">
                            {{ app()->getLocale() == 'ar' ? 'قاعدة المخزون:' : 'Inventory Rule:' }}
                        </h6>
                        <p class="text-slate-700 dark:text-slate-300 fs-8 mb-0">
                            {{ app()->getLocale() == 'ar' 
                                ? 'المخزون = بضائع دخلت إلى المنشأة بغرض البيع. يزيد المخزون عند الشراء، ويقل عند البيع أو التلف أو الاستبعاد.' 
                                : 'Inventory is stock acquired for resale. Increases on purchases, decreases on sales, damage, or scrap.' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3.5 rounded-4 bg-amber-500-10 border border-amber-500-30 d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-amber-500 text-white p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-amber-800 dark:text-amber-300 fs-7 mb-1">
                            {{ app()->getLocale() == 'ar' ? 'قاعدة الإهلاك:' : 'Depreciation Rule:' }}
                        </h6>
                        <p class="text-slate-700 dark:text-slate-300 fs-8 mb-0">
                            {{ app()->getLocale() == 'ar' 
                                ? 'الإهلاك لا يعني دفع مبلغ نقدي، بل هو توزيع تكلفة الأصل على عمره الإنتاجي. الأرض لا تهلك، بينما المباني والآلات والسيارات والأثاث تهلك.' 
                                : 'Depreciation is non-cash allocation of asset cost over lifespan. Land does not depreciate, while equipment & buildings do.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
