@php
    if (empty($minValue)) {
        $minValue = \App\Models\LanguageGuidelines::DISABLED;
    }
    $value = max($value, $minValue);
@endphp
<div
    title="{{ empty($disabled) 
        ? (empty($minValue) || $minValue === \App\Models\LanguageGuidelines::DISABLED) 
        ? __('content.triple_toggle_unlocked') : __('content.triple_toggle_locked_second_pos') 
        : __('content.triple_toggle_locked_third_pos')
    }}"
    class="tripple-toggle {{ $value === 2 ? 'active' : ($value === 1 ? 'middle active' : '') }}
    {{ empty($disabled) ? '' : ' disabled' }}"
    id="tripple-toggle-{!! $attributes->get('name') !!}"
    tabindex="0"
    role="button"
    aria-pressed="{{ $value === 2 ? 'true' : 'false' }}"
>
    @if($minValue === 1)
    <div class="triple-toggle-lock">
        @include('partials.triple_toggle_lock')
    </div>
    @endif
</div>
<input type="hidden" id="{!! $attributes->get('name') !!}" {!! $attributes->merge() !!} />

@if(empty($disabled))
<script>
    document.querySelector('#tripple-toggle-{!! $attributes->get('name') !!}').addEventListener('click', function() {
        toggle = document.querySelector('#tripple-toggle-{!! $attributes->get('name') !!}');
        hiddenInput = document.querySelector('#{!! $attributes->get('name') !!}');
        disabled = {{ (empty($minValue) || $minValue === \App\Models\LanguageGuidelines::DISABLED) ? 'true' : 'false' }};
        if (disabled) {
            if (toggle.classList.contains('active')) {
                if (toggle.classList.contains('middle')) {
                    toggle.classList.remove('middle');
                    hiddenInput.setAttribute('value', '2');
                } else {
                    toggle.classList.remove('active');
                    hiddenInput.setAttribute('value', '0');
                }
            } else {
                toggle.classList.add('active');
                toggle.classList.add('middle');
                hiddenInput.setAttribute('value', '1');
            }
        } else {
            if (toggle.classList.contains('middle')) {
                toggle.classList.remove('middle');
                hiddenInput.setAttribute('value', '2');
            } else {
                toggle.classList.add('active');
                toggle.classList.add('middle');
                hiddenInput.setAttribute('value', '1');
            }
        }

        hiddenInput.dispatchEvent(new Event('input'));
    });
</script>
@endif

<div class="lato-small-text-p" aria-live="polite">{!! $label !!}</div>

@include('partials.toggle_label', ['disabled' => $disabled ?? false])
