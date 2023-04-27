<div class="tripple-toggle" value="0" {{ empty($disabled) ? '' : 'disabled' }} {!! $attributes->merge(['id' => 'guidelines-form-section-toggle']) !!}></div>

<script>
    let toggle = document.querySelector('.tripple-toggle');
    toggle.addEventListener('click', function() {
        if (toggle.classList.contains('active')) {
            if (toggle.classList.contains('middle')) {
                toggle.classList.remove('middle');
                toggle.setAttribute('value', '2');
                
            } else {
                toggle.classList.remove('active');
                toggle.setAttribute('value', '0');
            }
        } else {
            toggle.classList.add('active');
            setTimeout(() => {
                toggle.classList.add('middle');
                toggle.setAttribute('value', '1');
            }, 150);
        }
    });
</script>

<div class="lato-small-text-p">{!! $label !!}</div>
@include('partials.toggle_label', ['disabled' => $disabled ?? false])

