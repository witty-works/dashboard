<x-list-section>
    <x-slot name="title">
        {{ __('rules.list_false_positives') }}
    </x-slot>

    <x-slot name="description">
        {{ __('rules.list_false_postives_description') }}
    </x-slot>

    <x-slot name="list">
        <table class="table-fixed w-full">
            <thead>
            <tr>
                <th class="px-4 py-2">{{ __('rules.false_positive_label') }}</th>
                @if (Auth::user()->hasTeamPermission($team, 'edit_rules'))
                <th class="px-4 py-2">{{ __('content.actions') }} </th>
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach ($list as $false_positive)
                <tr @if($loop->even)class="bg-grey"@endif>
                    <td class="border px-4 py-2 w-3/4">{{ $false_positive->false_positive }} {{ $false_positive->language_code ? "($false_positive->language_code)" : '' }} </td>
                    @if (Auth::user()->hasTeamPermission($team, 'edit_rules'))
                    <td class="border px-4 py-2">
                        <button wire:click="deleteFalsePositive({{ $false_positive->id }})" class="bg-red-100 text-red-600 px-6 rounded-full">
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