<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('scanner.title') }}
        </h2>
    </x-slot>

    <div class="md:grid md:grid-cols-3 md:gap-6">
        <x-jet-section-title>
            <x-slot name="title">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('scanner.categories') }}
                </h2>
            </x-slot>
            
            <x-slot name="description">
                @foreach($categories as $category => $subcategories)
                    <h2>{{ $category }}</h2>
                    <ul>
                    @foreach($subcategories as $subcategory => $count)
                        <li>{{ $subcategory }}: {{ $count }}</li>
                    @endforeach
                    </ul>
                @endforeach
            </x-slot>
        </x-jet-section-title>

        <div class="mt-5 md:mt-0 md:col-span-2">
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <form action="{{ route('scanner.show') }}" method="get">

                <x-jet-label for="url" value="{{ __('scanner.url') }}" />
                <x-jet-input id="url" name="url" type="text" class="mt-1 block w-full" autofocus value="{{ $url }}" />
                <x-jet-input-error for="url" class="mt-2" />
                <x-jet-button>
                    {{ __('scanner.submit') }}
                </x-jet-button>
                </form>
            </div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <x-jet-label for="text" value="{{ __('scanner.text') }} " />
                <textarea class="form-control block w-full">{{ $text }}</textarea>
            </div>
        </div>
    </div>
</x-app-layout>
