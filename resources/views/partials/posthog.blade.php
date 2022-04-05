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
    !function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.async=!0,p.src=s.api_host+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+".people (stub)"},o="capture identify alias people.set people.set_once set_config register register_once unregister opt_out_capturing has_opted_out_capturing opt_in_capturing reset isFeatureEnabled onFeatureFlags".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
    posthog.init(
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
</script>