<div>
    @if(isset($title) || isset($description) || isset($list))
    <x-section-title>
        @if(isset($title))
        <x-slot name="title">{{ $title }}</x-slot>
        @endif
        @if(isset($description))
        <x-slot name="description">{{ $description }}</x-slot>
        @endif
    </x-section-title>

    @if(isset($list))
    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="container align-left border-radius">
            {{ $list }}
        </div>
    </div>
    @endif
    @endif
</div>
