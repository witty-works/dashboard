@props(['submit', 'class' => '', 'enabled' => true])
<div>
    @if($enabled)
    <div class="{{ $class }}" role="region" aria-label="Form Section">
        <x-section-title>
            <x-slot name="title">{{ $title }}</x-slot>
            <x-slot name="description">{{ $description }}</x-slot>
        </x-section-title>

        <div>
            <form wire:submit="{{ $submit }}" class="w-full" aria-labelledby="form-title" aria-describedby="form-description">
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
