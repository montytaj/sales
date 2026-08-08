<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary px-3 py-2">
    <div class="container-fluid p-0">
        <!-- Sidebar Toggle & Brand -->
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-outline-light border-0 btn-sm" id="sidebarToggle" type="button" aria-label="Toggle Sidebar">
                <i class="bi bi-list fs-4"></i>
            </button>
            <a class="navbar-brand font-bold fs-5 d-flex align-items-center gap-2 mb-0" href="{{ route('dashboard') }}">
                <i class="bi bi-tools text-primary fs-4"></i>
                <span>{{ setting('facility_name', __('general.facility_name')) }}</span>
            </a>
        </div>

        <!-- Right Side Nav Items -->
        <div class="d-flex align-items-center gap-3 ms-auto">
            <!-- Reports Hub Quick Link -->
            <a href="{{ route('reports.index') }}" class="btn btn-outline-light btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-bar-chart-line-fill text-info"></i>
                <span class="d-none d-md-inline">مركز التقارير</span>
            </a>

            <!-- Notification Bell Icon with Unread Counter -->
            @auth
                @php
                    $unreadCount = app(\App\Services\NotificationService::class)->getUnreadCount(Auth::user());
                @endphp
                <div class="dropdown">
                    <a href="{{ route('notifications.index') }}" class="btn btn-outline-light border-0 btn-sm position-relative">
                        <i class="bi bi-bell fs-5"></i>
                        @if ($unreadCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </a>
                </div>
            @endauth

            <!-- Language Switcher Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center gap-1" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-globe"></i>
                    <span class="text-uppercase font-semibold">{{ app()->getLocale() }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="languageDropdown">
                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <li>
                            <a class="dropdown-menu-item dropdown-item d-flex align-items-center justify-content-between py-2 {{ app()->getLocale() === $localeCode ? 'active bg-primary text-white' : '' }}" 
                               rel="alternate" 
                               hreflang="{{ $localeCode }}" 
                               href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                                <span>{{ $properties['native'] }}</span>
                                <small class="text-uppercase opacity-75 ms-2">({{ $localeCode }})</small>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- User Profile Dropdown -->
            @auth
                <div class="dropdown">
                    <button class="btn btn-dark text-white border-0 dropdown-toggle d-flex align-items-center gap-2 p-1 pe-2" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center font-bold" style="width: 32px; height: 32px; font-size: 14px;">
                            {{ mb_substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <span class="d-none d-md-inline font-semibold text-sm">{{ Auth::user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="userDropdown">
                        <li class="px-3 py-2 border-bottom">
                            <div class="font-bold text-dark">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-muted">{{ Auth::user()->email }}</div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person text-muted"></i>
                                <span>{{ __('general.profile') }}</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('notifications.index') }}">
                                <i class="bi bi-bell text-muted"></i>
                                <span>مركز الإشعارات</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger">
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
</nav>
