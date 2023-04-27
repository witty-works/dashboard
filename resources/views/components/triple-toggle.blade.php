<div class="tripple-toggle {{ $value === 2 ? 'active' : ($value === 1 ? 'middle active' : '') }}" id="tripple-toggle-{!! $attributes->get('name') !!}" {{ empty($disabled) ? '' : 'disabled' }}></div>
<input type="hidden" id="{!! $attributes->get('name') !!}" {!! $attributes->merge() !!} />
<script>
    document.querySelector('#tripple-toggle-{!! $attributes->get('name') !!}').addEventListener('click', function() {
        toggle = document.querySelector('#tripple-toggle-{!! $attributes->get('name') !!}');
        hiddenInput = document.querySelector('#{!! $attributes->get('name') !!}');
        if (toggle.classList.contains('active')) {
            if (toggle.classList.contains('middle')) {
                toggle.classList.remove('middle');
                hiddenInput.setAttribute('value', '2');
                hiddenInput.dispatchEvent(new Event('input'));
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

<div class="lato-small-text-p">{!! $label !!}</div>
@include('partials.toggle_label', ['disabled' => $disabled ?? false])

