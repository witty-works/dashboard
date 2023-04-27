<x-list-section>
    @if($list->count())
    <x-slot name="title">
        {{ __('guidelines.list_team_term_replacements') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.list_team_term_replacements_description')) !!}
    </x-slot>

    <x-slot name="list">
        <table class="table-auto w-full">
            <thead>
                <tr>
                    <th class="py-2 lato-paragraph-text-p">{{ __('guidelines.term_label') }}</th>
                    <th class="py-2 lato-paragraph-text-p">{{ __('guidelines.replacement_label') }}</th>     
                    <th class="py-2 lato-paragraph-text-p">{{ __('guidelines.emoji_short_label') }}</th>
                    <th class="py-2 lato-paragraph-text-p">{{ __('guidelines.matching_type_label') }}</th>     
                    @if (Auth::user()->hasTeamPermission($model, 'edit_guidelines') && empty($hide_actions))
                    <th class="py-2 lato-paragraph-text-p">{{ __('guidelines.action_label') }}</th>     
                    @endif
                </tr>
            </thead>
            <tbody>
            @foreach ($list as $term_replacement)
            <tr @if($loop->even)class="bg-grey"@endif>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">{{ $term_replacement->term }}</td>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">{{ $term_replacement->replacement }}</td>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">{{ $term_replacement->emoji }}</td>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">
                    @if($term_replacement->matching_type === 'lemmatize')
                    {{ __('guidelines.'.$term_replacement->word_type) }}
                    @else
                    {{ __('guidelines.'.$term_replacement->matching_type) }}
                    @endif
                </td>
                @if (Auth::user()->hasTeamPermission($model, 'edit_guidelines') && empty($hide_actions))
                <td class="border px-4 py-2 text-center container-row">
                    <button onclick="document.getElementById('organization_term_replacements').scrollIntoView({behavior: 'smooth'});" wire:click="editTermReplacement({{ $term_replacement->id }})" class="button primary-button-red ">
                        {{ __('content.edit') }}
                    </button>
                    <button wire:click="deleteTermReplacement({{ $term_replacement->id }})" class="button secondary-button-red ">
                        {{ __('content.delete_permanently') }}
                    </button>
                </td>
                @endif
            </tr>
            @endforeach
            </tbody>
        </table>
    </x-slot>
    @endif
</x-list-section>