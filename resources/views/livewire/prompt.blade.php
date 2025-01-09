<x-form-section submit="sendPrompt">
    <x-slot name="title">
        {{ __('content.witty_prompt') }}
        @include('partials.beta')
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('content.prompt_description')) !!}
        <script>
            function copyToClipboard() {
                navigator.clipboard.writeText(document.getElementById('witty_response').innerText);
                document.getElementById('witty_response_copy_no').style.display = "none";
                document.getElementById('witty_response_copy_yes').style.display = "block";
                setTimeout(function(){
                    document.getElementById('witty_response_copy_yes').style.display = "none";
                    document.getElementById('witty_response_copy_no').style.display = "block";
                }, 300);
            }
        </script>
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
            <h2 class="mt-5 flex">
                {{ __('content.witty_response') }}
                &nbsp;
                <div class="relative pl-50 flex items-center">
                    <span onclick="copyToClipboard();" alt="{{ __('content.copy_to_clipboard')}}" class="flex items-center justify-center h-8 text-xs bg-white border rounded-md cursor-pointer w-9 border-neutral-200/60 hover:bg-neutral-100 active:bg-white focus:bg-white focus:outline-none text-neutral-500 hover:text-neutral-600 group">
                        <svg id="witty_response_copy_yes" style="display: none;" class="w-4 h-4 text-green-500 stroke-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        <svg id="witty_response_copy_no" style="display: block;" class="w-4 h-4 stroke-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g fill="none" stroke="none"><path d="M7.75 7.757V6.75a3 3 0 0 1 3-3h6.5a3 3 0 0 1 3 3v6.5a3 3 0 0 1-3 3h-.992" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M3.75 10.75a3 3 0 0 1 3-3h6.5a3 3 0 0 1 3 3v6.5a3 3 0 0 1-3 3h-6.5a3 3 0 0 1-3-3v-6.5z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></g></svg>
                    </span>
                </div>
            </h2>

            <div id="witty_response">
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