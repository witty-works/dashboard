<label class="switch">
    <input type="checkbox" {{ empty($disabled) ? '' : 'disabled' }} {!! $attributes->merge(['class' => 'guidelines-form-section-toggle']) !!} />
    <span class="slider round"></span>
</label>

<div class="lato-small-text-p">{{ $label }}</div>
@if(!empty($disabled))

  @if($disabled === 'locked')
      @include('partials.locked')
  @else
      <div class="p-3 whitespace-nowrap">
        @include('partials.witty-teams-only')
      </div>
  @endif

@endif
