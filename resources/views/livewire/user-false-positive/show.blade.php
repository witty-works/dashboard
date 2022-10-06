<x-list-section>
    <x-slot name="title">
        {{ __('guidelines.list_false_positives') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.list_false_positives_description')) !!}
    </x-slot>

    <x-slot name="list">
        <table class="table-fixed w-full">
            <thead>
                <tr>
                    <th class="py-2 lato-paragraph-text-p">{{ __('guidelines.false_positive_label') }}</th>
                    <th class="py-2 lato-paragraph-text-p">{{ __('guidelines.exists_on_team_label') }}</th>
                    <th class="py-2 lato-paragraph-text-p">{{ __('guidelines.action_label') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($list as $false_positive)
            <tr @if($loop->even)class="bg-grey"@endif>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">{{ $false_positive->false_positive }} {{ $false_positive->language_code ? "($false_positive->language_code)" : '' }} </td>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">{{ $false_positive->exists_on_team ? __('guidelines.yes') : __('guidelines.no') }} </td>
                <td class="border px-4 py-2 text-center container-row">
                    <button wire:click="editFalsePositive({{ $false_positive->id }})" class="button primary-button-red">
                        {{ __('content.edit') }}
                    </button>
                    <button wire:click="deleteFalsePositive({{ $false_positive->id }})"class="button secondary-button-red">
                        {{ __('content.delete_permanently') }}
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
        </table>
    </x-slot>
</x-list-section>