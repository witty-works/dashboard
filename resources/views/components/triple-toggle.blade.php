<div @if(empty($disabled)) title="{{ empty($minValue) || $minValue === \App\Models\LanguageGuidelines::DISABLED ? __('content.triple_toggle') : __('content.triple_toggle_no_disable') }}"@endif class="tripple-toggle {{ $value === 2 ? 'active' : ($value === 1 ? 'middle active' : '') }} {{ empty($disabled) ? '' : ' disabled' }}" id="tripple-toggle-{!! $attributes->get('name') !!}"></div>
<input type="hidden" id="{!! $attributes->get('name') !!}" {!! $attributes->merge() !!} />
@if(empty($disabled))
<script>
    document.querySelector('#tripple-toggle-{!! $attributes->get('name') !!}').addEventListener('click', function() {
        toggle = document.querySelector('#tripple-toggle-{!! $attributes->get('name') !!}');
        hiddenInput = document.querySelector('#{!! $attributes->get('name') !!}');
        @if(empty($minValue) || $minValue === \App\Models\LanguageGuidelines::DISABLED)
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
        @else
        if (toggle.classList.contains('middle')) {
            toggle.classList.remove('middle');
            hiddenInput.setAttribute('value', '2');
        } else {
            toggle.classList.add('active');
            toggle.classList.add('middle');
            hiddenInput.setAttribute('value', '1');
        }
        @endif

        hiddenInput.dispatchEvent(new Event('input'));
    });
</script>
@endif

<div class="lato-small-text-p">{!! $label !!}</div>
@include('partials.toggle_label', ['disabled' => $disabled ?? false])

