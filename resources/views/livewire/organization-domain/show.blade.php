<x-list-section>
    @if($list->count() && ($domain_list_type != 'allow_witty_works' || $hide_actions))
    <x-slot name="title">
        {{ __('guidelines.list_team_domains') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown($domain_list_type === 'deny' ? __('guidelines.list_team_deny_domains_description') : __('guidelines.list_team_allow_domains_description')) !!}
    </x-slot>

    <x-slot name="list">
        <table class="table-auto w-full">
            <caption class="sr-only">{{ __('guidelines.list_team_domains') }}</caption>
            <thead>
                <tr>
                    <th scope="col" class="py-2 lato-paragraph-text-p">{{ __('guidelines.domain_label') }}</th>
                    @if (Auth::user()->hasTeamPermission($model, 'edit_guidelines') && empty($hide_actions))
                    <th scope="col" class="py-2 lato-paragraph-text-p">{{ __('guidelines.action_label') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
            @foreach ($list as $domain)
            <tr @if($loop->even)class="bg-grey"@endif>
                <td class="border px-4 py-2 text-left">{{ $domain->domain }}</td>
                @if (Auth::user()->hasTeamPermission($model, 'edit_guidelines') && empty($hide_actions))
                <td class="border px-4 py-2 text-center container-row">
                    <button onclick="document.getElementById('organization_domains')?.scrollIntoView({behavior: 'smooth'});" wire:click="editDomain({{ $domain->id }})" class="button primary-button-red ">
                        {{ __('content.edit') }}
                    </button>
                    <button wire:click="deleteDomain({{ $domain->id }})" wire:confirm="{{ __('content.confirm_delete_entry') }}" class="button secondary-button-red ">
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
