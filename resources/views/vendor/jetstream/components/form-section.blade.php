@props(['submit', 'class' => "", 'enabled' => true])
@if($enabled)
<div class="{{ $class }}" role="region" aria-label="Form Section">
    <x-jet-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-jet-section-title>

    <div>
        <form wire:submit.prevent="{{ $submit }}" class="w-full" aria-labelledby="form-title" aria-describedby="form-description">
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
@else
    <div></div>
@endif