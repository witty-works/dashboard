<x-list-section>
    <x-slot name="title">
        {{ __('guidelines.list_term_replacements') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.list_term_replacements_description')) !!}
    </x-slot>

    <x-slot name="list">
        <table class="table-fixed w-full">
            <thead>
                <tr>
                    <th class="px-4 py-2">{{ __('guidelines.term_label') }}</th>
                    <th class="px-4 py-2">{{ __('guidelines.replacement_label') }}</th>     
                    <th class="px-4 py-2">{{ __('guidelines.emoji_short_label') }}</th>
                    <th class="px-4 py-2">{{ __('guidelines.exists_on_team_label') }}</th>
                    <th class="px-4 py-2">{{ __('guidelines.action_label') }}</th>     
                </tr>
            </thead>
            <tbody>
            @foreach ($list as $term_replacement)
            <tr @if($loop->even)class="bg-grey"@endif>
                <td class="border px-4 py-2 text-left">{{ $term_replacement->term }}</td>
                <td class="border px-4 py-2 text-left">{{ $term_replacement->replacement }}</td>
                <td class="border px-4 py-2 text-left">{{ $term_replacement->emoji }}</td>
                <td class="border px-4 py-2 text-left">{{ $term_replacement->exists_on_team ? __('guidelines.yes') : __('guidelines.no') }} </td>
                <td class="border px-4 py-2 text-center whitespace-nowrap">
                    <button wire:click="editTermReplacement({{ $term_replacement->id }})" class="bg-gray-100 text-gray-600 px-6 rounded-full">
                        {{ __('content.edit') }}
                    </button>
                    <button wire:click="deleteTermReplacement({{ $term_replacement->id }})" class="bg-red-100 text-red-600 px-6 rounded-full">
                        {{ __('content.delete_permanently') }}
                    </button>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </x-slot>
</x-list-section>