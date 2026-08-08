<x-list-section>
    @if($list->count())
    <x-slot name="title">
        {{ __('guidelines.list_domains') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.list_domains_description')) !!}
    </x-slot>

    <x-slot name="list">
        <table class="table-auto w-full">
            <caption class="sr-only">{{ __('guidelines.list_domains') }}</caption>
            <thead>
                <tr>
                    <th scope="col" class="py-2 lato-paragraph-text-p">{{ __('guidelines.domain_label') }}</th>
                    <th scope="col" class="py-2 lato-paragraph-text-p">{{ __('guidelines.exists_on_team_label') }}</th>
                    <th scope="col" class="py-2 lato-paragraph-text-p">{{ __('guidelines.action_label') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($list as $domain)
            <tr @if($loop->even)class="bg-grey"@endif>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">{{ $domain->domain }}</td>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">{{ $domain->exists_on_team ? __('guidelines.yes') : __('guidelines.no') }} </td>
                <td class="border px-4 py-2 text-center container-row">
                    <button onclick="document.getElementById('domains')?.scrollIntoView({behavior: 'smooth'});" wire:click="editDomain({{ $domain->id }})" class="button primary-button-red ">
                        {{ __('content.edit') }}
                    </button>
                    <button wire:click="deleteDomain({{ $domain->id }})" wire:confirm="{{ __('content.confirm_delete_entry') }}" class="button secondary-button-red ">
                        {{ __('content.delete_permanently') }}
                    </button>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </x-slot>
    @endif
</x-list-section>