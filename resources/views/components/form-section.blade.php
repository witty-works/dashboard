@props(['submit', 'class' => '', 'enabled' => true])
@php
    $sectionId = 'section-'.substr(md5((string) $title.(string) $description), 0, 8);
@endphp
<div>
    @if($enabled)
    <div class="{{ $class }}" role="region" aria-labelledby="{{ $sectionId }}-title">
        <x-section-title :id="$sectionId">
            <x-slot name="title">{{ $title }}</x-slot>
            <x-slot name="description">{{ $description }}</x-slot>
        </x-section-title>

        <div>
            <form wire:submit="{{ $submit }}" class="w-full" aria-labelledby="{{ $sectionId }}-title" aria-describedby="{{ $sectionId }}-description">
                @csrf
                @if (isset($actions))
                    <div class="container border-radius-top">
                        {{ $form }}
                    </div>
                    <div class="container light-grey-background border-radius-bottom">
                        {{ $actions }}
                    </div>
                @else
                <div class="container border-radius">
                    {{ $form }}
                </div>
                @endif
            </form>
        </div>
    </div>
    @endif
</div>
