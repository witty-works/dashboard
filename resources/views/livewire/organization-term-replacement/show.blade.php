<x-list-section>
    <x-slot name="title">
        {{ __('guidelines.list_team_term_replacements') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.list_team_term_replacements_description')) !!}
    </x-slot>

    <x-slot name="list">
        <table class="table-fixed w-full">
            <thead>
                <tr>
                    <th class="px-4 py-2">{{ __('guidelines.term_label') }}</th>
                    <th class="px-4 py-2">{{ __('guidelines.replacement_label') }}</th>     
                    <th class="px-4 py-2">{{ __('guidelines.emoji_short_label') }}</th>
                    @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines') && empty($hide_actions))
                    <th class="px-4 py-2">{{ __('guidelines.action_label') }}</th>     
                    @endif
                </tr>
            </thead>
            <tbody>
            @foreach ($list as $term_replacement)
            <tr @if($loop->even)class="bg-grey"@endif>
                <td class="border px-4 py-2 text-center">{{ $term_replacement->term }}</td>
                <td class="border px-4 py-2 text-center">{{ $term_replacement->replacement }}</td>
                <td class="border px-4 py-2 text-center">{{ $term_replacement->emoji }}</td>
                @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines') && empty($hide_actions))
                <td class="border px-4 py-2 text-center whitespace-nowrap">
                    <button wire:click="editTermReplacement({{ $term_replacement->id }})" class="bg-gray-100 text-gray-600 px-6 rounded-full">
                        {{ __('content.edit') }}
                    </button>
                    <button wire:click="deleteTermReplacement({{ $term_replacement->id }})" class="bg-red-100 text-red-600 px-6 rounded-full">
                        {{ __('content.delete_permanently') }}
                    </button>
                </td>
                @endif
            </tr>
            @endforeach
            </tbody>
        </table>
    </x-slot>
</x-list-section>