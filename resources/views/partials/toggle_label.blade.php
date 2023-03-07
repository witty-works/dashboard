@if(!empty($disabled))

  @if($disabled === 'locked' || $disabled === 'locked_upgrade')
      @include('partials.locked')
  @endif
  @if($disabled !== 'locked')
      <div class="p-3 whitespace-nowrap">
        @include('partials.witty-teams-only')
      </div>
  @endif

@endif