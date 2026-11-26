<nav class="mb-6 flex gap-6" aria-label="{{ __('content.admin_menu') }}">
    <a href="{{ route('admin.teams') }}" class="underline" @if (request()->routeIs('admin.teams')) aria-current="page" @endif>{{ __('content.admin_teams_title') }}</a>
    <a href="{{ route('admin.users') }}" class="underline" @if (request()->routeIs('admin.users')) aria-current="page" @endif>{{ __('content.admin_users_title') }}</a>
</nav>
