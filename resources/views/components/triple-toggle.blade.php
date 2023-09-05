<button
    type="button"
    aria-label="{{ empty($disabled) ? __('content.triple_toggle') : __('content.triple_toggle_disabled') }}"
    aria-disabled="{{ !empty($disabled) ? 'true' : 'false' }}"
    class="tripple-toggle {{ $value === 2 ? 'active' : ($value === 1 ? 'middle active' : '') }} {{ empty($disabled) ? '' : 'disabled' }}"
    id="tripple-toggle-{!! $attributes->get('id') !!}"
    {{ !empty($disabled) ? 'disabled' : '' }}>
</button>

<input type="hidden" id="{!! $attributes->get('id') !!}" {!! $attributes->merge()->filter(fn ($value, $key) => !in_array($key, ['label'])) !!} />

@if(empty($disabled))
<script>
    document.querySelector('#tripple-toggle-{!! $attributes->get('id') !!}').addEventListener('click', function() {
        let toggle = document.querySelector('#tripple-toggle-{!! $attributes->get('id') !!}');
        let hiddenInput = document.querySelector('#{!! $attributes->get('id') !!}');
        
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
            setTimeout(() => {
                toggle.classList.add('middle');
                hiddenInput.setAttribute('value', '1');
            }, 150);
        }

        hiddenInput.dispatchEvent(new Event('input'));
    });
</script>
@endif

<label class="lato-small-text-p" for=""tripple-toggle-{{$attributes->get("id") }}" aria-live="polite">{!! $label !!}</label>

@include('partials.toggle_label', ['disabled' => $disabled ?? false])
