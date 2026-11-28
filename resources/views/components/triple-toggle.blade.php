@php
    if (empty($minValue)) {
        $minValue = \App\Models\LanguageGuidelines::DISABLED;
    }
    $value = max($value, $minValue);
    $toggleName = $attributes->get('name');
    $toggleTitle = empty($disabled)
        ? (($minValue === \App\Models\LanguageGuidelines::DISABLED)
            ? __('content.triple_toggle_unlocked') : __('content.triple_toggle_locked_second_pos'))
        : __('content.triple_toggle_locked_third_pos');
    $fullyLocked = !empty($disabled) || $minValue === \App\Models\LanguageGuidelines::ADVANCED_ENABLED;
@endphp
<div
    title="{{ $toggleTitle }}"
    class="tripple-toggle {{ $value === 2 ? 'active' : ($value === 1 ? 'middle active' : '') }}
    {{ empty($disabled) ? '' : ' disabled' }}"
    id="tripple-toggle-{!! $toggleName !!}"
    tabindex="0"
    role="button"
    aria-pressed="{{ $value === 2 ? 'true' : ($value === 1 ? 'mixed' : 'false') }}"
    aria-labelledby="toggle-label-{!! $toggleName !!}"
    aria-describedby="toggle-desc-{!! $toggleName !!}"
    @if ($fullyLocked) aria-disabled="true" @endif
    {{-- resources/js/app.js handles clicks and keys on [data-triple-toggle]. --}}
    data-triple-toggle
    data-message-off="{{ __('guidelines.proficiency_level_off_toggle_message') }}"
    data-message-basic="{{ __('guidelines.proficiency_level_basic_toggle_message') }}"
    data-message-advanced="{{ __('guidelines.proficiency_level_advanced_toggle_message') }}"
    minvalue="{{ $minValue }}"
>
    @if($minValue === 1)
    <div class="triple-toggle-lock">
        @include('partials.triple_toggle_lock')
    </div>
    @endif
</div>

<div id="toggle-label-{!! $toggleName !!}" class="sr-only">
     {!! $label !!} </div>

<div id="toggle-desc-{!! $toggleName !!}" class="sr-only">{{ $toggleTitle }}</div>

<input type="hidden" id="{!! $toggleName !!}" {!! $attributes->merge() !!} />

<!-- Live region to announce changes for screen readers -->
<div id="toggle-announcement-{!! $toggleName !!}" class="sr-only" role="status" aria-live="polite"></div>


<div class="lato-small-text-p">{!! $label !!}</div>

@include('partials.toggle_label', ['disabled' => $disabled ?? false])