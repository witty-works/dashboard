<span class="m-3"
    onmouseover="
        setTimeout(function() {
            document.getElementById('{{ $category }}-image').style.zIndex = '1';
            document.getElementById('{{ $category }}-image').style.visibility = 'visible';
            document.getElementById('{{ $category }}-image').style.left = (event.clientX) + 'px';
            document.getElementById('{{ $category }}-image').style.top = (event.clientY + window.scrollY) + 'px';
        }, 50);"
    onmouseout="
        const allinformationImages = document.getElementsByClassName('information-image');
        for (let i = 0; i < allinformationImages.length; i++) {
            allinformationImages[i].style.zIndex = '-1';
            allinformationImages[i].style.visibility = 'hidden';
        }
    ">
    <img width="15" src="{{ asset('information-icon.svg') }}" alt="Info" />
</span>
<div id="{{ $category }}-image" class="information-image"
    onmouseover="
        const allinformationImages = document.getElementsByClassName('information-image');
        for (let i = 0; i < allinformationImages.length; i++) {
            allinformationImages[i].style.zIndex = '-1';
            allinformationImages[i].style.visibility = 'hidden';
        }
    ">
    <div class="information-title">{{ __('content.example') }}: {{ $name }}</div>
    @if(!empty($text))
    <div class="information-text">{!! $text !!}</div>
    @else
    <div class="information-text">
        @if(!empty($config['translation']['example_image_advanced']['src']))
        {{ __('guidelines.proficiency_level_basic') }}
        @endif
        <img src="{{ $config['translation']['example_image']['src'] }}" alt="{{ $config['translation']['example_image']['alt'] }}" width="400" />
    </div>
      @if(!empty($config['translation']['example_image_advanced']['src']))
    <div class="information-text">
        @if(!empty($config['translation']['example_image']['src']))
        {{ __('guidelines.proficiency_level_advanced') }}
        @endif
        <img src="{{ $config['translation']['example_image_advanced']['src'] }}" alt="{{ $config['translation']['example_image_advanced']['alt'] }}" width="400" />
    </div>
      @endif
    @endif
</div>
