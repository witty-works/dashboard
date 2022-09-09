<x-list-section>
    <x-slot name="title">
        {{ __('guidelines.list_team_false_positives') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.list_team_false_positives_description')) !!}
    </x-slot>

    <x-slot name="list">
        <table class="table-fixed w-full">
            <thead>
                <tr>
                    <th class="px-4 py-2">{{ __('guidelines.false_positive_label') }}</th>
                    @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines') && empty($hide_actions))
                    <th class="px-4 py-2">{{ __('guidelines.action_label') }}</th>     
                    @endif
                </tr>
            </thead>
            <tbody>
            @foreach ($list as $false_positive)
            <tr @if($loop->even)class="bg-grey"@endif>
                <td class="border px-4 py-2 text-left">{{ $false_positive->false_positive }} {{ $false_positive->language_code ? "($false_positive->language_code)" : '' }} </td>
                @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines') && empty($hide_actions))
                <td class="border px-4 py-2 text-center whitespace-nowrap">
                    <button wire:click="editFalsePositive({{ $false_positive->id }})" class="bg-gray-100 text-gray-600 px-6 rounded-full">
                        {{ __('content.edit') }}
                    </button>
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