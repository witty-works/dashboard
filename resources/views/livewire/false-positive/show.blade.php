<div>
    <table class="table-fixed w-full">
        <thead>
        <tr>
            <th class="px-4 py-2">{{ __('rules.false_positive_label') }}</th>
            <th class="px-4 py-2">{{ __('content.actions') }} </th>
        </tr>
        </thead>
        <tbody>
        @foreach ($list as $false_positive)
            <tr @if($loop->even)class="bg-grey"@endif>
                <td class="border px-4 py-2 w-3/4">{{ $false_positive->false_positive }} {{ $false_positive->language_code ? "($false_positive->language_code)" : '' }} </td>
                <td class="border px-4 py-2">
                    <button wire:click="deleteItem({{ $false_positive->id }})" class="bg-red-100 text-red-600 px-6 rounded-full">
                        Delete Permanently
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>