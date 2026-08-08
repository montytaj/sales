<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('financial.accounting_title') ?? 'الإدارة المحاسبية والشجرة']
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 w-100">
            <div>
                <h2 class="h4 mb-1 font-bold text-slate-900">
                    <i class="bi bi-diagram-3-fill text-primary me-2"></i>{{ __('financial.accounting_title') ?? 'الإدارة المحاسبية ودفتر القيود' }}
                </h2>
                <p class="text-muted fs-7 mb-0">إدارة الشجرة المحاسبية، إنشاء القيود اليومية المركبة، وتتبع التوازن المالي</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary font-semibold rounded-3 px-3 py-2 fs-7" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                    <i class="bi bi-folder-plus me-1"></i>إضافة حساب بدليل COA
                </button>
                <button type="button" class="btn btn-primary font-bold rounded-3 px-3 py-2 fs-7 shadow-sm" data-bs-toggle="modal" data-bs-target="#journalEntryModal">
                    <i class="bi bi-journal-plus me-1"></i>إضافة قيد يومية مركب
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Equal-Height Account Summary KPI Cards Grid (5 Categories) -->
    <div class="row g-3 mb-4">
        <!-- Assets Card -->
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <x-kpi-card 
                title="الأصول (Assets)" 
                :value="$accountStats['assets']" 
                currency="حساب" 
                subtitle="الأصول والموجودات" 
                icon="bi-bank" 
                color="primary" />
        </div>

        <!-- Liabilities Card -->
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <x-kpi-card 
                title="الالتزامات (Liabilities)" 
                :value="$accountStats['liabilities']" 
                currency="حساب" 
                subtitle="الخصوم والدائنون" 
                icon="bi-credit-card-2-front" 
                color="danger" />
        </div>

        <!-- Equity Card -->
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <x-kpi-card 
                title="حقوق الملكية (Equity)" 
                :value="$accountStats['equity']" 
                currency="حساب" 
                subtitle="رأس المال والأرباح" 
                icon="bi-pie-chart-fill" 
                color="purple" />
        </div>

        <!-- Revenue Card -->
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <x-kpi-card 
                title="الإيرادات (Revenue)" 
                :value="$accountStats['revenue']" 
                currency="حساب" 
                subtitle="المبيعات والعوائد" 
                icon="bi-graph-up-arrow" 
                color="emerald" />
        </div>

        <!-- Expense Card -->
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <x-kpi-card 
                title="المصروفات (Expenses)" 
                :value="$accountStats['expense']" 
                currency="حساب" 
                subtitle="التكاليف والنفقات" 
                icon="bi-receipt-cutoff" 
                color="amber" />
        </div>
    </div>

    <!-- Main Navigation Tabs: Journal Entries & Chart of Accounts Tree -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white border-bottom p-3">
            <ul class="nav nav-pills card-header-pills gap-2" id="accountingTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active font-bold rounded-3 px-3 py-2 fs-7" id="journal-entries-tab" data-bs-toggle="tab" data-bs-target="#journal-entries" type="button" role="tab">
                        <i class="bi bi-journal-text me-1.5"></i>دفتر القيود اليومية (Journal Entries)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link font-bold rounded-3 px-3 py-2 fs-7" id="tree-view-tab" data-bs-toggle="tab" data-bs-target="#tree-view" type="button" role="tab">
                        <i class="bi bi-diagram-3 me-1.5"></i>الشجرة المحاسبية التفاعلية (Chart of Accounts Tree)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link font-bold rounded-3 px-3 py-2 fs-7" id="accounts-table-tab" data-bs-toggle="tab" data-bs-target="#accounts-table" type="button" role="tab">
                        <i class="bi bi-table me-1.5"></i>جدول الحسابات الموحد
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-3 p-md-4 tab-content">
            
            <!-- Tab 1: Journal Entries List (With DataTables support) -->
            <div class="tab-pane fade show active" id="journal-entries" role="tabpanel">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-hover align-middle datatable w-100">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">{{ __('financial.entry_number') ?? 'رقم القيد' }}</th>
                                <th scope="col">التاريخ</th>
                                <th scope="col">الوصف والبيان</th>
                                <th scope="col">تفاصيل أطراف القيد (المدين / الدائن)</th>
                                <th scope="col">الحالة</th>
                                <th scope="col" class="text-end pe-3">إجراء الترحيل</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($journalEntries as $entry)
                                <tr>
                                    <td class="ps-3"><code class="fs-7 font-bold text-primary">{{ $entry->entry_number }}</code></td>
                                    <td class="font-mono fs-7">{{ $entry->entry_date->format('Y-m-d') }}</td>
                                    <td class="fw-semibold text-slate-800 fs-7">{{ $entry->description }}</td>
                                    <td>
                                        <div class="d-flex flex-column gap-1 fs-7">
                                            @foreach ($entry->lines as $line)
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-bold text-slate-700">{{ $line->account?->name ?? 'حساب غير معرّف' }}</span>
                                                    @if ($line->debit > 0)
                                                        <span class="badge bg-danger-subtle text-danger font-mono me-1">مدين: {{ number_format($line->debit, 2) }} {{ setting('currency', 'SAR') }}</span>
                                                    @endif
                                                    @if ($line->credit > 0)
                                                        <span class="badge bg-success-subtle text-success font-mono me-1">دائن: {{ number_format($line->credit, 2) }} {{ setting('currency', 'SAR') }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        @if ($entry->status === 'posted')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill"><i class="bi bi-lock-fill me-1"></i>مرحل ومحمي</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1.5 rounded-pill">مسودة</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        @if ($entry->status !== 'posted')
                                            <form method="POST" action="{{ route('accounting.post-entry', $entry) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success font-semibold rounded-pill px-3">
                                                    <i class="bi bi-check-all me-1"></i>ترحيل وحظر التعديل
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted fs-8 font-medium">مغلق للترحيل</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">لا توجد قيود يومية مسجلة حالياً.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Interactive Chart of Accounts Tree (UX/UI Redesign) -->
            <div class="tab-pane fade" id="tree-view" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-slate-600 fs-7">
                        <i class="bi bi-info-circle text-primary me-1"></i> استعرض الهيكل الهرمي للشجرة المحاسبية بجميع فروعها وتصنيفاتها.
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-light border font-semibold fs-7" onclick="toggleAllTree(true)">
                            <i class="bi bi-arrows-expand me-1"></i>توسيع الكل
                        </button>
                        <button type="button" class="btn btn-sm btn-light border font-semibold fs-7" onclick="toggleAllTree(false)">
                            <i class="bi bi-arrows-collapse me-1"></i>طي الكل
                        </button>
                    </div>
                </div>

                <div class="coa-tree-wrapper">
                    @forelse($rootAccounts as $root)
                        <div class="coa-tree-node mb-3 rounded-4 border bg-white shadow-2xs p-3">
                            <div class="d-flex align-items-center justify-content-between cursor-pointer" onclick="toggleTreeNode('node-{{ $root->id }}')">
                                <div class="d-flex align-items-center gap-2.5">
                                    <button class="btn btn-sm btn-light p-1 border-0" type="button">
                                        <i class="bi bi-chevron-down tree-icon text-slate-500" id="icon-node-{{ $root->id }}"></i>
                                    </button>
                                    <span class="badge bg-slate-900 text-white font-mono px-2.5 py-1.5 rounded-3 fs-7">{{ $root->code }}</span>
                                    <h6 class="mb-0 fw-bold text-slate-900 fs-6">{{ $root->name }}</h6>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $badgeBg = match($root->type) {
                                            'asset' => 'bg-primary-subtle text-primary border-primary-subtle',
                                            'liability' => 'bg-danger-subtle text-danger border-danger-subtle',
                                            'equity' => 'bg-purple-100 text-purple-700 border-purple-200',
                                            'revenue' => 'bg-success-subtle text-success border-success-subtle',
                                            'expense' => 'bg-warning-subtle text-warning border-warning-subtle',
                                            default => 'bg-slate-100 text-slate-700'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeBg }} border px-2.5 py-1 rounded-pill fs-8 font-semibold text-uppercase">
                                        {{ $root->type }}
                                    </span>
                                    <span class="badge bg-slate-100 text-slate-600 fs-8 font-mono">
                                        {{ $root->children->count() }} حساب فرعي
                                    </span>
                                </div>
                            </div>

                            <!-- Children list -->
                            @if($root->children->count() > 0)
                                <div class="tree-children-container mt-3 ps-4 border-start border-2 border-slate-200 ms-3" id="node-{{ $root->id }}">
                                    @foreach($root->children as $child)
                                        <div class="tree-child-item py-2 border-bottom border-slate-100 d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-diagram-2 text-slate-400 fs-7"></i>
                                                <span class="font-mono text-primary font-bold fs-7">{{ $child->code }}</span>
                                                <span class="fw-bold text-slate-800 fs-7">{{ $child->name }}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 fs-7">
                                                <span class="text-muted font-mono">{{ number_format($child->balance ?? 0, 2) }} {{ setting('currency', 'SAR') }}</span>
                                                <span class="badge bg-slate-100 text-slate-600 fs-8">{{ $child->is_selectable ? 'حساب فرعي مباشر' : 'رئيسي' }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">لا توجد حسابات رئيسية مسجلة في الشجرة.</div>
                    @endforelse
                </div>
            </div>

            <!-- Tab 3: Accounts Table View -->
            <div class="tab-pane fade" id="accounts-table" role="tabpanel">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-hover align-middle datatable w-100">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">رمز الحساب</th>
                                <th scope="col">اسم الحساب</th>
                                <th scope="col">تصنيف الحساب</th>
                                <th scope="col">الحساب الأب</th>
                                <th scope="col">الرصيد الحقيقي</th>
                                <th scope="col">الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($accounts as $acc)
                                <tr>
                                    <td class="ps-3"><code class="fs-7 font-bold text-primary">{{ $acc->code }}</code></td>
                                    <td class="fw-bold text-slate-800 fs-7">{{ $acc->name }}</td>
                                    <td><span class="badge bg-slate-100 text-slate-700 border px-2 py-1 fs-8 font-semibold text-uppercase">{{ $acc->type }}</span></td>
                                    <td class="text-slate-600 fs-7">{{ $acc->parent?->name ?? '-' }}</td>
                                    <td class="font-mono font-bold text-slate-900 fs-7">{{ number_format($acc->balance ?? 0, 2) }} {{ setting('currency', 'SAR') }}</td>
                                    <td><span class="badge bg-success-subtle text-success">نشط</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">لا توجد حسابات مسجلة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Compound Journal Entry Modal (القيود المركبة المحاسبية متعددة الأطراف) -->
    <div class="modal fade" id="journalEntryModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form method="POST" action="{{ route('accounting.store-journal-entry') }}" id="compoundJournalForm">
                    @csrf
                    <div class="modal-header bg-slate-900 text-white p-3.5">
                        <h5 class="modal-title font-bold d-flex align-items-center gap-2 fs-6">
                            <i class="bi bi-journal-plus text-primary fs-4"></i>إضافة قيد يومية مركب (Compound Journal Entry)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 bg-slate-50/50">
                        <!-- Top Metadata -->
                        <div class="row g-3 mb-4 bg-white p-3.5 rounded-4 border shadow-2xs">
                            <div class="col-12 col-md-4">
                                <label for="entry_date" class="form-label font-semibold fs-7 text-slate-700">تاريخ القيد المحاسبي <span class="text-danger">*</span></label>
                                <input type="date" name="entry_date" id="entry_date" class="form-control fs-7" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-12 col-md-8">
                                <label for="entry_desc" class="form-label font-semibold fs-7 text-slate-700">البيان والشرح العام للقيد <span class="text-danger">*</span></label>
                                <input type="text" name="description" id="entry_desc" class="form-control fs-7" required placeholder="قيد التسوية اليومية، إثبات مبيعات ومصروفات اليوم...">
                            </div>
                        </div>

                        <!-- Dynamic Lines Table Header & Live Balance Indicator -->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="font-bold text-slate-900 mb-0">
                                <i class="bi bi-list-nested text-primary me-1"></i> تفاصيل وأطراف القيد المركب (Multiple Debits & Credits)
                            </h6>
                            <div id="entryBalanceIndicator" class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fs-7 font-bold">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> غير متوازن (الفارق: 0.00)
                            </div>
                        </div>

                        <!-- Lines Table -->
                        <div class="table-responsive rounded-4 border bg-white mb-3">
                            <table class="table table-bordered align-middle mb-0" id="compoundLinesTable">
                                <thead class="table-light fs-7">
                                    <tr>
                                        <th style="width: 35%;">الحساب المعني <span class="text-danger">*</span></th>
                                        <th style="width: 20%;">مدين (DEBIT)</th>
                                        <th style="width: 20%;">دائن (CREDIT)</th>
                                        <th style="width: 20%;">البيان الفرعي</th>
                                        <th style="width: 5%;" class="text-center">حذف</th>
                                    </tr>
                                </thead>
                                <tbody id="journalLinesBody">
                                    <!-- Row 1: Default Debit Line -->
                                    <tr class="journal-line-row">
                                        <td>
                                            <select name="lines[0][account_id]" class="form-select fs-7 account-select" required>
                                                <option value="">-- اختر الحساب --</option>
                                                @foreach ($accounts as $a)
                                                    <option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }} ({{ $a->type }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="lines[0][debit]" class="form-control fs-7 debit-input font-mono" value="0.00" oninput="calculateEntryTotals()">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="lines[0][credit]" class="form-control fs-7 credit-input font-mono" value="0.00" oninput="calculateEntryTotals()">
                                        </td>
                                        <td>
                                            <input type="text" name="lines[0][description]" class="form-control fs-7" placeholder="بيان فرعي...">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeJournalRow(this)"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>

                                    <!-- Row 2: Default Credit Line -->
                                    <tr class="journal-line-row">
                                        <td>
                                            <select name="lines[1][account_id]" class="form-select fs-7 account-select" required>
                                                <option value="">-- اختر الحساب --</option>
                                                @foreach ($accounts as $a)
                                                    <option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }} ({{ $a->type }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="lines[1][debit]" class="form-control fs-7 debit-input font-mono" value="0.00" oninput="calculateEntryTotals()">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="lines[1][credit]" class="form-control fs-7 credit-input font-mono" value="0.00" oninput="calculateEntryTotals()">
                                        </td>
                                        <td>
                                            <input type="text" name="lines[1][description]" class="form-control fs-7" placeholder="بيان فرعي...">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeJournalRow(this)"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light font-bold fs-7">
                                    <tr>
                                        <td class="text-end">الإجمالي:</td>
                                        <td class="text-danger font-mono fs-6" id="totalDebitCell">0.00 {{ setting('currency', 'SAR') }}</td>
                                        <td class="text-success font-mono fs-6" id="totalCreditCell">0.00 {{ setting('currency', 'SAR') }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Add Line Button -->
                        <button type="button" class="btn btn-sm btn-outline-primary font-bold rounded-3 px-3" onclick="addJournalRow()">
                            <i class="bi bi-plus-circle me-1"></i> إضافة طرف جديد للقيد (طرف إضافي)
                        </button>
                    </div>
                    <div class="modal-footer bg-white p-3">
                        <button type="button" class="btn btn-light font-semibold" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary font-bold px-4" id="submitJournalBtn" disabled>
                            <i class="bi bi-check-circle me-1"></i> حفظ وتدقيق توازن القيد المركب
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Account Modal -->
    <div class="modal fade" id="addAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <form method="POST" action="{{ route('accounting.store-account') }}">
                    @csrf
                    <div class="modal-header bg-slate-900 text-white">
                        <h5 class="modal-title font-bold fs-6"><i class="bi bi-folder-plus me-1.5 text-primary"></i>إضافة حساب بدليل الحسابات</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="acc_code" class="form-label font-semibold fs-7">رمز الحساب (Code) <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="acc_code" class="form-control fs-7 font-mono" required placeholder="مثال: 1101, 2101...">
                        </div>
                        <div class="mb-3">
                            <label for="acc_name" class="form-label font-semibold fs-7">اسم الحساب المحاسبي <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="acc_name" class="form-control fs-7" required placeholder="مثال: البنك الأهلي، مبيعات المعرض...">
                        </div>
                        <div class="mb-3">
                            <label for="acc_type" class="form-label font-semibold fs-7">تصنيف الحساب <span class="text-danger">*</span></label>
                            <select name="type" id="acc_type" class="form-select fs-7" required>
                                <option value="asset">أصول (Asset)</option>
                                <option value="liability">التزامات ودائنون (Liability)</option>
                                <option value="equity">حقوق ملكية (Equity)</option>
                                <option value="revenue">إيرادات ومبيعات (Revenue)</option>
                                <option value="expense">مصروفات وتكاليف (Expense)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="parent_id" class="form-label font-semibold fs-7">الحساب الأب (إن وجد)</label>
                            <select name="parent_id" id="parent_id" class="form-select fs-7">
                                <option value="">-- حساب رئيسي (بدون أب) --</option>
                                @foreach ($accounts as $a)
                                    <option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light font-semibold" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary font-bold">حفظ الحساب</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts for Compound Journal Entry Dynamic Lines & Tree Toggle -->
    <script>
        let lineIndex = 2;

        function toggleTreeNode(nodeId) {
            const container = document.getElementById(nodeId);
            const icon = document.getElementById('icon-' + nodeId);
            if (container) {
                if (container.style.display === 'none') {
                    container.style.display = 'block';
                    if (icon) icon.className = 'bi bi-chevron-down tree-icon text-slate-500';
                } else {
                    container.style.display = 'none';
                    if (icon) icon.className = 'bi bi-chevron-left tree-icon text-slate-500';
                }
            }
        }

        function toggleAllTree(expand) {
            const containers = document.querySelectorAll('.tree-children-container');
            containers.forEach(c => {
                c.style.display = expand ? 'block' : 'none';
            });
        }

        function addJournalRow() {
            const tbody = document.getElementById('journalLinesBody');
            const newRow = document.createElement('tr');
            newRow.className = 'journal-line-row';

            const accountOptions = `@foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }} ({{ $a->type }})</option>@endforeach`;

            newRow.innerHTML = `
                <td>
                    <select name="lines[${lineIndex}][account_id]" class="form-select fs-7 account-select" required>
                        <option value="">-- اختر الحساب --</option>
                        ${accountOptions}
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="lines[${lineIndex}][debit]" class="form-control fs-7 debit-input font-mono" value="0.00" oninput="calculateEntryTotals()">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="lines[${lineIndex}][credit]" class="form-control fs-7 credit-input font-mono" value="0.00" oninput="calculateEntryTotals()">
                </td>
                <td>
                    <input type="text" name="lines[${lineIndex}][description]" class="form-control fs-7" placeholder="بيان فرعي...">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeJournalRow(this)"><i class="bi bi-trash"></i></button>
                </td>
            `;

            tbody.appendChild(newRow);
            lineIndex++;
            calculateEntryTotals();
        }

        function removeJournalRow(btn) {
            const rows = document.querySelectorAll('.journal-line-row');
            if (rows.length <= 2) {
                alert('يجب أن يحتوي القيد المركب على طرفين على الأقل (مدين ودائن).');
                return;
            }
            btn.closest('tr').remove();
            calculateEntryTotals();
        }

        function calculateEntryTotals() {
            let totalDebit = 0;
            let totalCredit = 0;

            document.querySelectorAll('.debit-input').forEach(i => {
                totalDebit += parseFloat(i.value) || 0;
            });

            document.querySelectorAll('.credit-input').forEach(i => {
                totalCredit += parseFloat(i.value) || 0;
            });

            const currency = '{{ setting("currency", "SAR") }}';
            document.getElementById('totalDebitCell').innerText = totalDebit.toFixed(2) + ' ' + currency;
            document.getElementById('totalCreditCell').innerText = totalCredit.toFixed(2) + ' ' + currency;

            const diff = Math.abs(totalDebit - totalCredit);
            const indicator = document.getElementById('entryBalanceIndicator');
            const submitBtn = document.getElementById('submitJournalBtn');

            if (diff < 0.01 && totalDebit > 0) {
                indicator.className = 'badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fs-7 font-bold';
                indicator.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> القيد متوازن ومستعد للحفظ';
                submitBtn.disabled = false;
            } else {
                indicator.className = 'badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fs-7 font-bold';
                indicator.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> القيد غير متوازن (الفارق: ' + diff.toFixed(2) + ' ' + currency + ')';
                submitBtn.disabled = true;
            }
        }
    </script>
</x-app-layout>

