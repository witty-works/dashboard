<x-form-section submit="sendPrompt">
    <x-slot name="title">
        {{ __('content.witty_prompt') }}
        @include('partials.beta')
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('content.prompt_description')) !!}
    </x-slot>

    <x-slot name="form">
        @if(!Auth::user()->planId())
            @include('partials.trial-ended', ['user' => Auth::user()])
        @elseif(!Auth::user()->currentTeam->llm_alternatives)
            {!! Str::markdown(__('content.enable_llm', ['url' => route('teams.privacy-settings')])) !!}
        @else
        <div class="w-full col-span-6 sm:col-span-4">
            <label for="prompt">{{ __('content.prompt') }}</label>

            <x-input id="prompt"
                type="textarea"
                class="mt-1 block w-full"
                wire:model="prompt"
                autocomplete="off"
                aria-autocomplete="none" />

            <x-input-error wire:loading.remove for="prompt" class="mt-2" aria-describedby="prompt" />
        </div>

        @if($witty_response)
        <div class="align-middle items-center" wire:loading.remove>
            <h2 class="mt-5">{{ __('content.witty_response') }}</h2>
            <div>
                {!! nl2br($witty_response) !!}
            </div>

            @if(!empty($results))
            <h2 class="mt-5">{{ __('content.witty_issues') }}</h2>
            <div>
                <ul>
                @foreach($results as $result)
                    <li>
                        <span class="underlined" style="text-decoration-color: {{ \App\Livewire\Prompt::getColor($result)}}">{{ $result['text'] }}</span>
                        -
                        {{ $result['explanation']['text'] }}
                        (<a href="{{ $result['explanation']['url'] }}" target="_new">{{ \App\Livewire\Prompt::getSubcategoryLabel($result) }}</a>)
                    </li>
                @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($diff))
            <h2 class="mt-5">{{ __('content.witty_edits') }}</h2>
            <div>
                {!! $diff !!}
            </div>
            @endif
        </div>
        @endif
        @endif
    </x-slot>

    <x-slot name="actions">
        @if(Auth::user()->planId() && Auth::user()->currentTeam->llm_alternatives)
        <div class="flex flex-row align-middle items-center" role="toolbar" aria-label="Action buttons">
            <x-button>
                {{ __('content.submit') }}
            </x-button>

            <div wire:loading class="m-3"> 
                {{ __('content.send_prompt') }}
            </div>
        </div>
        @endif
    </x-slot>
</x-form-section>