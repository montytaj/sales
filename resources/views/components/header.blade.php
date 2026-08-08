<header class="app-header navbar navbar-expand navbar-light bg-white border-bottom sticky-top shadow-sm px-3">
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-2">
            <!-- Sidebar Toggle Button (Mobile & Desktop) -->
            <button class="btn btn-outline-secondary btn-sm border-0 px-2 py-1" type="button" id="sidebarToggle" aria-label="{{ __('general.toggle_navigation') }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('general.toggle_navigation') }}">
                <i class="bi bi-list fs-4 text-slate-700"></i>
            </button>

            <!-- Brand Title / Logo -->
            <a class="navbar-brand font-bold d-flex align-items-center gap-2 me-0 ms-1" href="{{ route('dashboard') }}">
                @php $sysLogo = setting('logo'); @endphp
                @if($sysLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($sysLogo))
                    <img src="{{ asset('storage/' . $sysLogo) }}" alt="{{ setting('facility_name', 'ERP') }}" class="img-fluid object-contain" style="max-height: 36px; max-width: 140px;">
                @else
                    <div class="rounded-circle bg-primary-subtle p-1 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="bi bi-tools text-primary fs-5"></i>
                    </div>
                    <span class="fs-5 font-bold text-slate-800 tracking-tight d-none d-sm-inline">
                        {{ setting('facility_name', config('app.name', 'Workshop ERP')) }}
                    </span>
                @endif
            </a>
        </div>

        <!-- Right Header Actions -->
        <div class="d-flex align-items-center ms-auto gap-2 gap-md-3">
            <!-- Branch Selector -->
            @auth
                @php
                    $accessibleBranches = Auth::user()->accessibleBranches();
                    $currentBranch = Auth::user()->mainBranch ?? $accessibleBranches->first();
                @endphp
                @if($accessibleBranches->count() > 0)
                    <div class="dropdown d-none d-sm-block">
                        <button class="btn btn-sm btn-light border d-flex align-items-center gap-2 rounded-pill px-3 py-1.5" type="button" id="branchDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-building text-primary"></i>
                            <span class="font-medium text-slate-700 fs-7">{{ $currentBranch?->name ?? __('branches.title') }}</span>
                            @if($accessibleBranches->count() > 1)
                                <i class="bi bi-chevron-down fs-8 text-muted"></i>
                            @endif
                        </button>
                        @if($accessibleBranches->count() > 1)
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-lg" aria-labelledby="branchDropdown">
                                <li><h6 class="dropdown-header">{{ __('branches.select_branch') ?? 'اختر الفرع' }}</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                @foreach($accessibleBranches as $b)
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $currentBranch?->id == $b->id ? 'active bg-primary-subtle text-primary font-semibold' : '' }}" href="#">
                                            <span>{{ $b->name }}</span>
                                            @if($currentBranch?->id == $b->id)
                                                <i class="bi bi-check-lg text-primary me-2"></i>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            @endauth

            <!-- Notifications Center -->
            @auth
                @php
                    $unreadCount = 0;
                    $recentNotifications = collect();
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('system_notifications')) {
                            app(\App\Services\NotificationService::class)->generateSystemAlerts(Auth::user());
                            $unreadCount = \App\Models\SystemNotification::where('user_id', Auth::id())->where('is_read', false)->count();
                            $recentNotifications = \App\Models\SystemNotification::where('user_id', Auth::id())->latest()->take(6)->get();
                        }
                    } catch (\Throwable $e) {
                        $unreadCount = 0;
                    }
                @endphp
                <div class="dropdown">

                    <button class="btn btn-sm btn-light border-0 position-relative rounded-circle p-2 d-flex align-items-center justify-content-center" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="width: 38px; height: 38px;">
                        <i class="bi bi-bell fs-5 text-slate-600"></i>
                        @if($unreadCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.65rem;">
                                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-md border-0 rounded-lg p-0" aria-labelledby="notificationsDropdown" style="width: 340px; max-height: 420px; overflow-y: auto;">
                        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-slate-50 rounded-top-lg">
                            <h6 class="mb-0 font-bold text-slate-800">{{ __('general.notifications') }}</h6>
                            @if($unreadCount > 0)
                                <form action="{{ route('notifications.mark-all-as-read') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-link p-0 text-decoration-none fs-7 font-medium text-primary">
                                        {{ __('general.mark_all_read') ?? 'تحديد الكل كمقروء' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                        
                        @if($recentNotifications->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($recentNotifications as $notif)
                                    <a href="{{ route('notifications.mark-read', ['locale' => app()->getLocale(), 'notification' => $notif->id]) }}" class="list-group-item list-group-item-action p-3 d-flex align-items-start gap-2.5 {{ !$notif->is_read ? 'bg-primary-subtle bg-opacity-25' : '' }}">
                                        <div class="rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0 {{ $notif->priority == 'high' ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary' }}" style="width: 32px; height: 32px;">
                                            <i class="bi {{ $notif->type == 'inventory' ? 'bi-box-seam' : 'bi-exclamation-circle-fill' }} fs-7"></i>
                                        </div>
                                        <div class="w-100 overflow-hidden">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="font-bold text-slate-800 fs-7 text-truncate d-block">{{ $notif->title }}</span>
                                                <span class="fs-8 text-muted ms-1 flex-shrink-0">{{ $notif->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="mb-0 fs-8 text-slate-600 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.3;">
                                                {{ $notif->message }}
                                            </p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="p-3 text-center text-muted fs-7">
                                <i class="bi bi-bell-slash fs-3 d-block mb-1 opacity-50"></i>
                                <span>{{ __('general.no_new_notifications') ?? 'لا توجد إشعارات جديدة' }}</span>
                            </div>
                        @endif

                        <div class="p-2 border-top text-center bg-slate-50 rounded-bottom-lg">
                            <a href="{{ route('notifications.index') }}" class="fs-7 text-primary font-semibold text-decoration-none">
                                {{ __('general.view_all_notifications') ?? 'عرض جميع الإشعارات' }}
                            </a>
                        </div>
                    </ul>
                </div>

            @endauth

            <!-- Language Switcher -->
            <div class="dropdown">
                <button class="btn btn-sm btn-light border-0 d-flex align-items-center gap-1.5 rounded-pill px-2.5 py-1.5" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-globe text-slate-600"></i>
                    <span class="font-medium text-slate-700 fs-7 text-uppercase">{{ app()->getLocale() }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-lg" aria-labelledby="languageDropdown">
                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $localeCode == app()->getLocale() ? 'active bg-primary-subtle text-primary font-semibold' : '' }}" href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                                <span>{{ $properties['native'] }}</span>
                                <span class="badge bg-secondary-subtle text-secondary text-uppercase me-2">{{ $localeCode }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- User Dropdown Menu -->
            @auth
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border d-flex align-items-center gap-2 rounded-pill px-2.5 py-1" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        @if(Auth::user()->avatar)
                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="rounded-circle object-fit-cover" style="width: 32px; height: 32px;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center font-bold fs-7" style="width: 32px; height: 32px;">
                                {{ mb_substr(Auth::user()->name, 0, 1) }}
                            </div>
                        @endif
                        <span class="d-none d-md-inline font-semibold text-slate-800 fs-7 me-1">{{ Auth::user()->name }}</span>
                        <i class="bi bi-chevron-down fs-8 text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-md border-0 rounded-lg p-2" aria-labelledby="userMenuDropdown" style="min-width: 220px;">
                        <li class="px-3 py-2 bg-slate-50 rounded-md mb-2">
                            <div class="font-bold text-slate-800 fs-6">{{ Auth::user()->name }}</div>
                            <div class="text-muted fs-7 text-truncate">{{ Auth::user()->email }}</div>
                            <div class="mt-1">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-medium fs-8">
                                    {{ Auth::user()->roles->first()?->name ?? __('general.administrator') }}
                                </span>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 rounded-md py-2 text-slate-700" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person-gear text-primary"></i> 
                                <span>{{ __('general.profile') }}</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item d-flex align-items-center gap-2 rounded-md py-2 text-danger">
                                    <i class="bi bi-box-arrow-right"></i> 
                                    <span>{{ __('general.logout') }}</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endauth
        </div>
    </div>
</header>

