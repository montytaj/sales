@php
    $hasChildren = $account->children && $account->children->count() > 0;
    
    // Level-based styling and RTL-friendly indentation
    $levelIndent = match($account->level) {
        1 => 'border-end border-4 border-primary bg-primary-subtle bg-opacity-10 my-2 rounded-3 shadow-sm p-3',
        2 => 'ms-3 me-3 border-end border-3 border-info my-1 bg-white rounded-3 p-2.5 shadow-2xs',
        3 => 'ms-4 me-4 border-end border-2 border-secondary my-1 bg-light bg-opacity-50 rounded-2 p-2',
        4 => 'ms-5 me-5 border-end border-2 border-slate-300 my-1 bg-white rounded-2 p-2',
        5 => 'ms-5 me-5 border-end border-3 border-success my-1 bg-success-subtle bg-opacity-10 rounded-2 p-2 border border-success-subtle',
        default => 'p-2 my-1',
    };

    $levelBadge = match($account->level) {
        1 => 'bg-primary text-white',
        2 => 'bg-info text-white',
        3 => 'bg-secondary text-white',
        4 => 'bg-dark text-white',
        5 => 'bg-success text-white',
        default => 'bg-secondary',
    };

    $typeBadge = match($account->type) {
        'asset' => 'bg-blue-100 text-blue-800 border-blue-200',
        'liability' => 'bg-red-100 text-red-800 border-red-200',
        'equity' => 'bg-purple-100 text-purple-800 border-purple-200',
        'revenue' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'expense' => 'bg-amber-100 text-amber-800 border-amber-200',
        default => 'bg-gray-100 text-gray-800',
    };

    $typeName = match($account->type) {
        'asset' => app()->getLocale() == 'ar' ? 'أصول' : 'Asset',
        'liability' => app()->getLocale() == 'ar' ? 'خصوم' : 'Liability',
        'equity' => app()->getLocale() == 'ar' ? 'حقوق ملكية' : 'Equity',
        'revenue' => app()->getLocale() == 'ar' ? 'إيرادات' : 'Revenue',
        'expense' => app()->getLocale() == 'ar' ? 'مصروفات' : 'Expense',
        default => $account->type,
    };
@endphp

<li class="list-group-item border-0 p-0 tree-node-item" data-account-code="{{ $account->code }}" data-account-name="{{ $account->name }}">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 {{ $levelIndent }} transition hover-shadow-sm">
        <!-- Account Info & Title -->
        <div class="d-flex align-items-center gap-2">
            @if($hasChildren)
                <button class="btn btn-sm btn-light border p-1 text-primary shadow-2xs rounded-2 d-flex align-items-center justify-content-center" 
                        style="width: 28px; height: 28px;"
                        type="button" 
                        onclick="toggleTreeNode({{ $account->id }}, event)"
                        title="{{ app()->getLocale() == 'ar' ? 'توسيع / طي الحسابات الفرعية' : 'Toggle Sub-Accounts' }}">
                    <i class="bi bi-folder-plus fs-6" id="folderIcon{{ $account->id }}"></i>
                </button>
            @else
                <div class="p-1 text-muted d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                    <i class="bi bi-file-earmark-text text-secondary fs-6"></i>
                </div>
            @endif

            <span class="badge {{ $levelBadge }} font-mono fs-7 rounded-2 px-2 py-1">
                L{{ $account->level }}
            </span>
            <span class="badge bg-dark-subtle text-dark font-mono fs-7 rounded-2 px-2 py-1 border border-secondary-subtle">
                {{ $account->code }}
            </span>

            <span class="fw-bold {{ $account->level == 1 ? 'fs-6 text-slate-900' : ($account->level == 5 ? 'text-success font-bold' : 'text-slate-800') }}">
                {{ $account->name }}
            </span>

            @if($account->level == 1)
                <span class="badge {{ $typeBadge }} fs-7 rounded-pill border ms-1 px-2.5">
                    {{ $typeName }}
                </span>
            @endif

            @if($account->is_selectable)
                <span class="badge bg-success text-white fs-7 rounded-pill px-2.5">
                    <i class="bi bi-check2-circle me-1"></i>{{ app()->getLocale() == 'ar' ? 'حساب إجرائي تفيصلي' : 'Operational' }}
                </span>
            @endif
        </div>

        <!-- Balance & Meta Actions -->
        <div class="d-flex align-items-center gap-3">
            <span class="fs-7 text-muted">
                {{ app()->getLocale() == 'ar' ? 'الطبيعة:' : 'Nature:' }} 
                @if($account->nature == 'debit')
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-7 px-2">
                        {{ app()->getLocale() == 'ar' ? 'مدين' : 'Debit' }}
                    </span>
                @else
                    <span class="badge bg-success-subtle text-success border border-success-subtle fs-7 px-2">
                        {{ app()->getLocale() == 'ar' ? 'دائن' : 'Credit' }}
                    </span>
                @endif
            </span>

            <div class="font-mono bg-white text-dark px-3 py-1 rounded-3 border shadow-2xs">
                <small class="text-muted me-1 fs-7">{{ app()->getLocale() == 'ar' ? 'الرصيد:' : 'Bal:' }}</small>
                <strong class="fs-6 {{ $account->balance < 0 ? 'text-danger' : 'text-slate-900' }}">{{ number_format($account->balance, 2) }}</strong>
            </div>

            @if($account->level < 5)
                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-2" 
                        data-bs-toggle="modal" 
                        data-bs-target="#createAccountModal" 
                        onclick="prefillParentAccount({{ $account->id }}, '{{ $account->code }}', '{{ addslashes($account->name) }}', {{ $account->level }})"
                        title="{{ app()->getLocale() == 'ar' ? 'إضافة حساب فرعي تحت هذا الحساب' : 'Add Child Account' }}">
                    <i class="bi bi-plus-lg me-1"></i><small>{{ app()->getLocale() == 'ar' ? 'إضافة فرعي' : 'Add Child' }}</small>
                </button>
            @endif
        </div>
    </div>

    @if($hasChildren)
        <ul class="list-group list-group-flush border-0 ps-0 pe-0 child-tree-container d-none" id="treeChildContainer{{ $account->id }}">
            @foreach($account->children as $child)
                @include('accounting.tree_item', ['account' => $child, 'level' => $child->level])
            @endforeach
        </ul>
    @endif
</li>
