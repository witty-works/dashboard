@if (config('posthog.enabled'))
@php
$user = Auth::user();
if ($user) {
    $identifyData = $user->posthogId();

    $userData = [
        'email' => $user->email,
        'name' => $user->name,
    ];

    $team = $user->currentTeam;
    if ($team) {
        $companyData = ['name' => $team->name];
    }
}
@endphp
<script>
    if (window.posthog) {
        window.posthog.init(
            {!! json_encode(config('posthog.api_key')) !!}, {
            api_host: {!! json_encode(config('posthog.host'), JSON_UNESCAPED_SLASHES) !!},
            loaded: function(posthog) {
                @if (\App\Http\Middleware\PostHogMiddleware::$reset)
                posthog.reset();
                @endif
                @if (!empty($identifyData))
                posthog.identify(
                    {!! json_encode($identifyData) !!}
                );
                @endif

                @if (!empty($userData))
                posthog.people.set({!! json_encode($userData) !!});
                posthog.group(
                    {!! json_encode(\App\Http\Middleware\PostHogMiddleware::POSTHOG_USER_TYPE) !!},
                    {!! json_encode($identifyData) !!},
                    {!! json_encode($userData) !!}
                );
                @endif

                @if (!empty($team))
                posthog.group(
                    {!! json_encode(\App\Http\Middleware\PostHogMiddleware::POSTHOG_ORGANIZATION_TYPE) !!},
                    {!! json_encode($team->posthogId()) !!},
                    {!! json_encode($companyData) !!}
                );
                @endif
            }
        });
    }
</script>
@endif