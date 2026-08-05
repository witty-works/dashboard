@if(!empty($disabled && !is_bool($disabled)))
  @if($disabled === 'locked')
      @include('partials.locked')
  @endif
@endif
