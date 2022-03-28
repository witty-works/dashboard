<x-list-section>
    <x-slot name="title">
        {{ __('guidelines.list_term_replacements') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.list_term_replacements_description')) !!}
    </x-slot>

    <x-slot name="list">
        <table class="table-fixed w-full">
            @foreach ($list as $term_replacement)
            <tr @if($loop->even)class="bg-grey"@endif>
                <td class="border px-4 py-2 w-2/5">{{ $term_replacement->term }} {{ $term_replacement->language_code ? "($term_replacement->language_code)" : '' }} </td>
                <td class="border px-4 py-2 w-2/5">{{ $term_replacement->replacement }} {{ $term_replacement->language_code ? "($term_replacement->language_code)" : '' }} </td>
                @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
                <td class="border px-4 py-2">
                    <button wire:click="deleteTermReplacement({{ $term_replacement->id }})" class="bg-red-100 text-red-600 px-6 rounded-full">
                        {{ __('content.delete_permanently') }}
                    </button>
                </td>
                @endif
            </tr>
            @endforeach
        </table>
    </x-slot>
</x-list-section>