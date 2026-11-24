<nav x-data="{ open: false }" class="bg-navy-950 border-b border-navy-800 sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-navy-700 to-navy-900 border border-beige-400/40 flex items-center justify-center shadow-lg text-beige-300">
                            <svg class="w-5 h-5 text-beige-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="text-lg font-extrabold text-white tracking-tight">Cargo<span class="text-beige-300">Trace</span></span>
                            <span class="hidden sm:inline-block text-[10px] uppercase font-mono tracking-wider ml-1.5 px-1.5 py-0.5 rounded bg-navy-800 text-beige-200 border border-navy-700">Enterprise</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-8 sm:flex">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-navy-800 text-beige-200 shadow-sm border border-navy-700' : 'text-slate-300 hover:text-white hover:bg-navy-900' }}">
                        <svg class="w-4 h-4 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('shipments.index') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('shipments.*') ? 'bg-navy-800 text-beige-200 shadow-sm border border-navy-700' : 'text-slate-300 hover:text-white hover:bg-navy-900' }}">
                        <svg class="w-4 h-4 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Shipments
                    </a>
                    @if (Auth::user()->isAdmin() || Auth::user()->isOperator())
                        <a href="{{ route('carriers.index') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('carriers.*') ? 'bg-navy-800 text-beige-200 shadow-sm border border-navy-700' : 'text-slate-300 hover:text-white hover:bg-navy-900' }}">
                            <svg class="w-4 h-4 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            Carriers
                        </a>
                        <a href="{{ route('locations.index') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('locations.*') ? 'bg-navy-800 text-beige-200 shadow-sm border border-navy-700' : 'text-slate-300 hover:text-white hover:bg-navy-900' }}">
                            <svg class="w-4 h-4 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            Ports & Hubs
                        </a>
                    @endif
                    @if (Auth::user()->isAdmin())
                        <a href="{{ route('audit-logs.index') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('audit-logs.*') ? 'bg-navy-800 text-beige-200 shadow-sm border border-navy-700' : 'text-slate-300 hover:text-white hover:bg-navy-900' }}">
                            <svg class="w-4 h-4 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Audit Trail
                        </a>
                    @endif
                </div>
            </div>

            <!-- Right: Notifications & User Menu -->
            <div class="hidden sm:flex sm:items-center sm:space-x-4">
                <!-- Notifications Bell -->
                <a href="{{ route('notifications.index') }}" class="relative p-2 text-slate-300 hover:text-white hover:bg-navy-900 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    @php $unreadCount = Auth::user()->unreadNotifications()->count(); @endphp
                    @if ($unreadCount > 0)
                        <span class="absolute top-1 right-1 flex h-4 w-4">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-4 w-4 bg-amber-500 text-[9px] font-bold text-navy-950 items-center justify-center">{{ $unreadCount }}</span>
                        </span>
                    @endif
                </a>

                <!-- User Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-1.5 border border-navy-800 text-sm font-medium rounded-xl text-beige-100 bg-navy-900 hover:bg-navy-800 focus:outline-none transition">
                            <div class="text-left mr-2">
                                <div class="font-medium text-xs text-white">{{ Auth::user()->name }}</div>
                                <div class="text-[10px] uppercase font-mono tracking-wider text-beige-300">{{ Auth::user()->role }}</div>
                            </div>
                            <svg class="fill-current h-4 w-4 text-slate-400" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-gray-100 text-xs text-gray-500">
                            Signed in as <strong class="text-gray-800">{{ Auth::user()->email }}</strong>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('My Profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-white hover:bg-navy-900">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-navy-900 border-b border-navy-800">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-white">
                Dashboard
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('shipments.index')" :active="request()->routeIs('shipments.*')" class="text-white">
                Shipments
            </x-responsive-nav-link>
            @if (Auth::user()->isAdmin() || Auth::user()->isOperator())
                <x-responsive-nav-link :href="route('carriers.index')" :active="request()->routeIs('carriers.*')" class="text-white">
                    Carriers
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('locations.index')" :active="request()->routeIs('locations.*')" class="text-white">
                    Ports & Hubs
                </x-responsive-nav-link>
            @endif
            @if (Auth::user()->isAdmin())
                <x-responsive-nav-link :href="route('audit-logs.index')" :active="request()->routeIs('audit-logs.*')" class="text-white">
                    Audit Trail
                </x-responsive-nav-link>
            @endif
        </div>
        <div class="pt-4 pb-1 border-t border-navy-800">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-slate-400">{{ Auth::user()->email }} ({{ Auth::user()->role }})</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-slate-300">
                    Profile
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-slate-300">
                        Log Out
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
