@php
    $role = auth()->user()->role;
    $canPolicyManage = in_array($role, ['SUPER_ADMIN', 'BRANCH_MANAGER', 'UNDERWRITER'], true);
    $canClaimsManage = in_array($role, ['SUPER_ADMIN', 'BRANCH_MANAGER', 'CLAIMS_OFFICER'], true);
    $canFinance = in_array($role, ['SUPER_ADMIN', 'BRANCH_MANAGER', 'ACCOUNTANT'], true);
    $canReports = in_array($role, ['SUPER_ADMIN', 'BRANCH_MANAGER', 'UNDERWRITER', 'ACCOUNTANT', 'CLAIMS_OFFICER'], true);
    $showCRM = in_array($role, ['SUPER_ADMIN', 'BRANCH_MANAGER', 'BROKER'], true);
@endphp

<aside class="w-full border-b border-brand-900/30 bg-[linear-gradient(160deg,#0b1c45_0%,#0d2f71_55%,#0f766e_100%)] text-brand-50 lg:w-72 lg:min-h-screen lg:border-b-0 lg:border-s lg:border-brand-900/50">
    <div class="flex items-center justify-between px-5 py-4 lg:block">
        <div>
            <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-sm font-extrabold text-white ring-1 ring-white/30">NI</div>
            <p class="text-xs font-medium tracking-wide text-brand-100/80">Namaa Consulting</p>
            <h2 class="text-lg font-extrabold text-white">Insurance Platform</h2>
        </div>

        <button class="rounded-lg border border-white/40 px-2 py-1 text-xs font-semibold lg:hidden" @click="navOpen = ! navOpen">{{ __('messages.menu') }}</button>
    </div>

    <nav class="space-y-1 px-3 pb-4" :class="navOpen ? 'block' : 'hidden lg:block'">
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'sidebar-active' : '' }}">{{ __('messages.dashboard') }}</a>

        @if ($canPolicyManage || in_array($role, ['BROKER', 'CLIENT'], true))
            <a href="{{ route('policies.index') }}" class="sidebar-link {{ request()->routeIs('policies.*') ? 'sidebar-active' : '' }}">{{ __('messages.policies') }}</a>
        @endif

        @if ($canClaimsManage || in_array($role, ['BROKER', 'CLIENT'], true))
            <a href="{{ route('claims.index') }}" class="sidebar-link {{ request()->routeIs('claims.*') ? 'sidebar-active' : '' }}">{{ __('messages.claims') }}</a>
        @endif

        @if ($showCRM)
            <a href="{{ route('crm.index') }}" class="sidebar-link {{ request()->routeIs('crm.*') ? 'sidebar-active' : '' }}">{{ __('messages.crm') }}</a>
        @endif

        @if ($canFinance)
            <a href="{{ route('reinsurance.index') }}" class="sidebar-link {{ request()->routeIs('reinsurance.*') ? 'sidebar-active' : '' }}">{{ __('messages.reinsurance') }}</a>
            <a href="{{ route('finance.index') }}" class="sidebar-link {{ request()->routeIs('finance.*') ? 'sidebar-active' : '' }}">{{ __('messages.finance') }}</a>
        @endif

        @if ($canReports)
            <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'sidebar-active' : '' }}">{{ __('messages.reports') }}</a>
        @endif

        <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'sidebar-active' : '' }}">{{ __('messages.settings') }}</a>

        <div class="pt-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full rounded-xl bg-red-500 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-600">{{ __('messages.logout') }}</button>
            </form>
        </div>
    </nav>
</aside>
