<span class="ml-3"
    onmouseover="
        setTimeout(function() {
            document.getElementById('{{ $category }}-image').style.zIndex = '1';
            document.getElementById('{{ $category }}-image').style.visibility = 'visible';
            document.getElementById('{{ $category }}-image').style.left = (event.clientX) + 'px';
            document.getElementById('{{ $category }}-image').style.top = (event.clientY + window.scrollY) + 'px';
        }, 200);"
    onmouseout="
        document.getElementById('{{ $category }}-image').style.zIndex = '-1';
        document.getElementById('{{ $category }}-image').style.visibility = 'hidden';
">
    <img width="15" src="{{ asset('information-icon.svg') }}" alt="Info" />
</span>
<div id="{{ $category }}-image" class="information-image">
    <div class="information-text">{{ __('content.example') }}: {{ $name }}</div>
    <img src="{{ $config['translation']['example_image']['src'] }}" alt="{{ $config['translation']['example_image']['alt'] }}" width=400/>
</div>