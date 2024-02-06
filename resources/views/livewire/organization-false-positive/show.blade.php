<x-list-section>
    @if($list->count())
    <x-slot name="title">
        {{ __('guidelines.list_team_false_positives') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.list_team_false_positives_description')) !!}
    </x-slot>

    <x-slot name="list">
        <table class="table-auto w-full">
            <thead>
                <tr>
                    <th class="py-2 lato-paragraph-text-p">{{ __('guidelines.false_positive_label') }}</th>
                    @if (Auth::user()->hasTeamPermission($model, 'edit_guidelines') && empty($hide_actions))
                    <th class="py-2 lato-paragraph-text-p">{{ __('guidelines.action_label') }}</th>     
                    @endif
                </tr>
            </thead>
            <tbody>
            @foreach ($list as $false_positive)
            <tr @if($loop->even)class="bg-grey"@endif>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">{{ $false_positive->false_positive }} {{ $false_positive->language_code ? "($false_positive->language_code)" : '' }} </td>
                @if (Auth::user()->hasTeamPermission($model, 'edit_guidelines') && empty($hide_actions))
                <td class="border px-4 py-2 text-center container-row">
                    <button onclick="document.getElementById('organization_false_positives')?.scrollIntoView({behavior: 'smooth'});" wire:click="editFalsePositive({{ $false_positive->id }})" class="button primary-button-red ">
                        {{ __('content.edit') }}
                    </button>
                    <button wire:click="deleteFalsePositive({{ $false_positive->id }})" class="button secondary-button-red ">
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