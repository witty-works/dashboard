@if(!empty($disabled && !is_bool($disabled)))
<!-- TODO: REMOVE THIS ONLY FOR CUSTOMIZE WITTY PAGE
  @if($disabled === 'locked' || $disabled === 'locked_upgrade')
      @include('partials.locked')
  @endif -->
  @if($disabled !== 'locked')
      <div class="p-3 whitespace-nowrap">
        @include('partials.witty-teams-only')
      </div>
  @endif

@endif