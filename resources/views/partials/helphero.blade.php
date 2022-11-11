@if (config('helphero.js_enabled'))
@php
  $user = Auth::user();
@endphp
<script src="//app.helphero.co/embed/{{ config('helphero.app_id') }}"></script>
<script>
    if (window.HelpHero) {
        @if(empty($user))
        HelpHero.anonymous();
        @else
        HelpHero.identify({!! json_encode($user->posthogId()) !!}, {!! json_encode($user->getHubspotData()) !!});
        window.addEventListener('helpHeroUpdate', (e) => {
          HelpHero.reset();
          HelpHero.identify({!! json_encode($user->posthogId()) !!}, e.detail.helpHeroData);
        });
        @endif
    }
</script>
@endif