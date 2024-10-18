@php
    if (empty($minValue)) {
        $minValue = \App\Models\LanguageGuidelines::DISABLED;
    }
    $value = max($value, $minValue);
@endphp
<div
    title="{{ empty($disabled) 
        ? ($minValue === \App\Models\LanguageGuidelines::DISABLED) 
        ? __('content.triple_toggle_unlocked') : __('content.triple_toggle_locked_second_pos') 
        : __('content.triple_toggle_locked_third_pos')
    }}"
    class="tripple-toggle {{ $value === 2 ? 'active' : ($value === 1 ? 'middle active' : '') }}
    {{ empty($disabled) ? '' : ' disabled' }}"
    id="tripple-toggle-{!! $attributes->get('name') !!}"
    tabindex="0"
    role="button"
    aria-pressed="{{ $value === 2 ? 'true' : 'false' }}"
    aria-labelledby="toggle-label-{!! $attributes->get('name') !!}"
    onclick="handleToggle(this)"
    onkeydown="handleKeyDown(this, event)"
    minvalue="{{ $minValue }}"
>
    @if($minValue === 1)
    <div class="triple-toggle-lock">
        @include('partials.triple_toggle_lock')
    </div>
    @endif
</div>

<div id="toggle-label-{!! $attributes->get('name') !!}" class="sr-only">
     {!! $label !!} </div>

<input type="hidden" id="{!! $attributes->get('name') !!}" {!! $attributes->merge() !!} />

<!-- Live region to announce changes for screen readers -->
<div id="toggle-announcement" class="sr-only" aria-live="polite"></div>

@if(empty($disabled))
<script>
    function handleKeyDown(toggle, event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();  
            handleToggle(toggle); 
        }
    }

    function handleToggle(toggle) {
        let hiddenInput = document.querySelector(`#${toggle.id.replace('tripple-toggle-', '')}`);
        let announcement = document.getElementById('toggle-announcement');
        let currentState = hiddenInput.value;
        
        switch (toggle.getAttribute('minvalue')) {
        case '{{\App\Models\LanguageGuidelines::DISABLED}}':
            if (toggle.classList.contains('active')) {
                if (toggle.classList.contains('middle')) {
                    toggle.classList.remove('middle');
                    hiddenInput.setAttribute('value', '2');
                    toggle.setAttribute('aria-pressed', 'true');
                    announcement.innerText =  "{{ __('guidelines.proficiency_level_advanced_toggle_message') }}"; // Announce "on" status
                } else {
                    toggle.classList.remove('active');
                    hiddenInput.setAttribute('value', '0');
                    toggle.setAttribute('aria-pressed', 'false');
                    announcement.innerText = "{{ __('guidelines.proficiency_level_off_toggle_message') }}"; // Announce "off" status
                }
            } else {
                toggle.classList.add('active');
                toggle.classList.add('middle');
                hiddenInput.setAttribute('value', '1');
                toggle.setAttribute('aria-pressed', 'false');
                announcement.innerText = "{{ __('guidelines.proficiency_level_basic_toggle_message') }}"; // Announce "middle" status
            }
            break;
        case '{{\App\Models\LanguageGuidelines::BASIC_ENABLED}}':
            if (toggle.classList.contains('middle')) {
                toggle.classList.remove('middle');
                hiddenInput.setAttribute('value', '2');
                toggle.setAttribute('aria-pressed', 'true');
                announcement.innerText = "{{ __('guidelines.proficiency_level_advanced_toggle_message') }}";
            } else {
                toggle.classList.add('active');
                toggle.classList.add('middle');
                hiddenInput.setAttribute('value', '1');
                toggle.setAttribute('aria-pressed', 'false');
                announcement.innerText = "{{ __('guidelines.proficiency_level_off_toggle_message') }}";
            }
            break;
        case '{{\App\Models\LanguageGuidelines::ADVANCED_ENABLED}}':
            return
        }

        hiddenInput.dispatchEvent(new Event('input'));
    }
</script>
@endif

<div class="lato-small-text-p" aria-live="polite">{!! $label !!}</div>

@include('partials.toggle_label', ['disabled' => $disabled ?? false])