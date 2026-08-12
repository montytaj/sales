<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h4 mb-1 font-bold text-slate-900">
                    <i class="bi bi-diagram-3-fill text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'شجرة الحسابات المحاسبية' : 'Chart of Accounts' }}
                </h2>
                <p class="text-muted fs-7 mb-0">
                    {{ app()->getLocale() == 'ar' ? 'الدليل المحاسبي الشجري خماسي المستويات لإدارة الأصول والخصوم والحسابات الإجرائية' : '5-Level Interactive Chart of Accounts for Financial Management' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary shadow-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                    <i class="bi bi-plus-circle me-1"></i>{{ app()->getLocale() == 'ar' ? 'إضافة حساب جديد' : 'Add New Account' }}
                </button>
            </div>
        </div>
    </x-slot>

    @php
        $totalAccountsCount = \App\Models\Account::count();
        $level1Count = \App\Models\Account::where('level', 1)->count();
        $level5Count = \App\Models\Account::where('level', 5)->count();
    @endphp

    <!-- Stats Banner -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white hover-lift transition">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 font-bold d-block mb-1">{{ app()->getLocale() == 'ar' ? 'إجمالي الحسابات' : 'Total Accounts' }}</span>
                        <h4 class="fw-bold text-slate-900 font-mono mb-0">{{ $totalAccountsCount }}</h4>
                        <small class="text-primary fs-7 font-bold">{{ app()->getLocale() == 'ar' ? 'دليل خماسي المستويات' : '5 Hierarchy Levels' }}</small>
                    </div>
                    <div class="rounded-3 p-3 bg-primary-subtle text-primary">
                        <i class="bi bi-diagram-3 fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white hover-lift transition">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 font-bold d-block mb-1">{{ app()->getLocale() == 'ar' ? 'الحسابات الرئيسية (المستوى 1)' : 'Root Accounts (L1)' }}</span>
                        <h4 class="fw-bold text-slate-900 font-mono mb-0">{{ $level1Count }} <small class="fs-7 text-muted">{{ app()->getLocale() == 'ar' ? 'حسابات رئيسية' : 'Roots' }}</small></h4>
                        <small class="text-info fs-7 font-bold">{{ app()->getLocale() == 'ar' ? 'الأصول، الخصوم، الملكية، الإيرادات، المصروفات' : 'Assets, Liabilities, Equity, etc.' }}</small>
                    </div>
                    <div class="rounded-3 p-3 bg-info-subtle text-info">
                        <i class="bi bi-folder-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white hover-lift transition">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 font-bold d-block mb-1">{{ app()->getLocale() == 'ar' ? 'الحسابات الإجرائية التفصيلية (المستوى 5)' : 'Operational Accounts (L5)' }}</span>
                        <h4 class="fw-bold text-slate-900 font-mono mb-0">{{ $level5Count }} <small class="fs-7 text-muted">{{ app()->getLocale() == 'ar' ? 'حساب تفصيلي' : 'Operational' }}</small></h4>
                        <small class="text-success fs-7 font-bold">{{ app()->getLocale() == 'ar' ? 'حسابات القيد والعمليات المحاسبية' : 'Direct Transaction Target' }}</small>
                    </div>
                    <div class="rounded-3 p-3 bg-success-subtle text-success">
                        <i class="bi bi-check2-square fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accounts Tree Card Container -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3 border-bottom border-slate-100">
            <div class="d-flex align-items-center gap-3 flex-grow-1" style="max-width: 400px;">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="treeSearchInput" class="form-control bg-light border-start-0" placeholder="{{ app()->getLocale() == 'ar' ? 'بحث بكود أو اسم الحساب...' : 'Search by code or account name...' }}" onkeyup="filterAccountTree()">
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="expandAllTree()">
                    <i class="bi bi-arrows-expand me-1"></i>{{ app()->getLocale() == 'ar' ? 'توسيع الكل' : 'Expand All' }}
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="collapseAllTree()">
                    <i class="bi bi-arrows-collapse me-1"></i>{{ app()->getLocale() == 'ar' ? 'طي الكل' : 'Collapse All' }}
                </button>
            </div>
        </div>

        <div class="card-body p-4">
            <ul class="list-group list-group-flush border-0 ps-0 pe-0" id="accountsTreeRoot">
                @foreach($accounts as $acc1)
                    @include('accounting.tree_item', ['account' => $acc1, 'level' => 1])
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Modal Create Account -->
    <div class="modal fade" id="createAccountModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="{{ route('accounting.store') }}" method="POST">
                @csrf
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title font-bold text-slate-900">
                            <i class="bi bi-plus-circle text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'إضافة حساب جديد إلى شجرة الحسابات' : 'Add New Account' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label font-bold text-slate-800">{{ app()->getLocale() == 'ar' ? 'الحساب الأب' : 'Parent Account' }}</label>
                                <select name="parent_id" id="accountParentSelect" class="form-select" onchange="updateAccountLevel(this)">
                                    <option value="" data-level="1" data-type="asset" data-nature="debit">
                                        {{ app()->getLocale() == 'ar' ? '-- حساب رئيسي مستوى 1 --' : '-- Root Account Level 1 --' }}
                                    </option>
                                    @foreach($allAccounts as $pAcc)
                                        @if($pAcc->level < 5)
                                            <option value="{{ $pAcc->id }}" data-level="{{ $pAcc->level + 1 }}" data-type="{{ $pAcc->type }}" data-nature="{{ $pAcc->nature }}">
                                                [{{ $pAcc->code }}] {{ $pAcc->name }} ({{ app()->getLocale() == 'ar' ? 'مستوى ' . $pAcc->level : 'Level ' . $pAcc->level }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-bold text-slate-800">{{ app()->getLocale() == 'ar' ? 'كود الحساب' : 'Account Code' }} <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="accountCodeInput" class="form-control font-mono" placeholder="111103" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-bold text-slate-800">{{ app()->getLocale() == 'ar' ? 'اسم الحساب' : 'Account Name' }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="{{ app()->getLocale() == 'ar' ? 'مثال: حساب البنك الأهلي' : 'e.g. Al-Ahli Bank' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-bold text-slate-800">{{ app()->getLocale() == 'ar' ? 'نوع الحساب الرئيسي' : 'Account Type' }}</label>
                                <select name="type" id="accountTypeSelect" class="form-select" required>
                                    <option value="asset">{{ app()->getLocale() == 'ar' ? 'أصول' : 'Asset' }}</option>
                                    <option value="liability">{{ app()->getLocale() == 'ar' ? 'خصوم / التزامات' : 'Liability' }}</option>
                                    <option value="equity">{{ app()->getLocale() == 'ar' ? 'حقوق ملكية' : 'Equity' }}</option>
                                    <option value="revenue">{{ app()->getLocale() == 'ar' ? 'إيرادات' : 'Revenue' }}</option>
                                    <option value="expense">{{ app()->getLocale() == 'ar' ? 'مصروفات' : 'Expense' }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-bold text-slate-800">{{ app()->getLocale() == 'ar' ? 'طبيعة الحساب المحاسبية' : 'Nature' }}</label>
                                <select name="nature" id="accountNatureSelect" class="form-select" required>
                                    <option value="debit">{{ app()->getLocale() == 'ar' ? 'مدين' : 'Debit' }}</option>
                                    <option value="credit">{{ app()->getLocale() == 'ar' ? 'دائن' : 'Credit' }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-bold text-slate-800">{{ app()->getLocale() == 'ar' ? 'مستوى الحساب' : 'Account Level' }}</label>
                                <input type="number" name="level" id="accountLevelInput" class="form-control bg-light font-mono" value="1" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Cancel' }}</button>
                        <button type="submit" class="btn btn-primary font-bold px-4">{{ app()->getLocale() == 'ar' ? 'حفظ الحساب' : 'Save Account' }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleTreeNode(id, event) {
            if (event) event.stopPropagation();
            const container = document.getElementById('treeChildContainer' + id);
            const icon = document.getElementById('folderIcon' + id);
            
            if (container) {
                if (container.classList.contains('d-none')) {
                    container.classList.remove('d-none');
                    if (icon) {
                        icon.classList.remove('bi-folder-plus');
                        icon.classList.add('bi-folder-minus');
                    }
                } else {
                    container.classList.add('d-none');
                    if (icon) {
                        icon.classList.remove('bi-folder-minus');
                        icon.classList.add('bi-folder-plus');
                    }
                }
            }
        }

        function expandAllTree() {
            document.querySelectorAll('.child-tree-container').forEach(el => {
                el.classList.remove('d-none');
            });
            document.querySelectorAll('[id^="folderIcon"]').forEach(icon => {
                icon.classList.remove('bi-folder-plus');
                icon.classList.add('bi-folder-minus');
            });
        }

        function collapseAllTree() {
            document.querySelectorAll('.child-tree-container').forEach(el => {
                el.classList.add('d-none');
            });
            document.querySelectorAll('[id^="folderIcon"]').forEach(icon => {
                icon.classList.remove('bi-folder-minus');
                icon.classList.add('bi-folder-plus');
            });
        }

        function filterAccountTree() {
            const query = document.getElementById('treeSearchInput').value.toLowerCase().trim();
            const items = document.querySelectorAll('.tree-node-item');
            
            if (query === '') {
                items.forEach(el => el.style.display = '');
                return;
            }

            expandAllTree();

            items.forEach(el => {
                const code = el.getAttribute('data-account-code') || '';
                const name = el.getAttribute('data-account-name') || '';
                
                if (code.toLowerCase().includes(query) || name.toLowerCase().includes(query)) {
                    el.style.display = '';
                } else {
                    el.style.display = 'none';
                }
            });
        }

        function prefillParentAccount(parentId, parentCode, parentName, parentLevel) {
            const select = document.getElementById('accountParentSelect');
            if (select) {
                select.value = parentId;
                updateAccountLevel(select);
            }
        }

        function updateAccountLevel(select) {
            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt) {
                const targetLevel = selectedOpt.getAttribute('data-level') || 1;
                const targetType = selectedOpt.getAttribute('data-type');
                const targetNature = selectedOpt.getAttribute('data-nature');

                document.getElementById('accountLevelInput').value = targetLevel;
                if (targetType) document.getElementById('accountTypeSelect').value = targetType;
                if (targetNature) document.getElementById('accountNatureSelect').value = targetNature;
            }
        }
    </script>
</x-app-layout>
