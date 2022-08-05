<div>
    @if ($domain_list_type != 'allow_witty_works' || $hide_actions)
    <x-list-section>
        <x-slot name="title">
            {{ __('guidelines.list_team_domains') }}
        </x-slot>

        <x-slot name="description">
            {!! Str::markdown($domain_list_type === 'deny' ? __('guidelines.list_team_deny_domains_description') : __('guidelines.list_team_allow_domains_description')) !!}
        </x-slot>

        <x-slot name="list">
            <table class="table-fixed w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2">{{ __('guidelines.domain_label') }}</th>
                        @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines') && empty($hide_actions))
                        <th class="px-4 py-2">{{ __('guidelines.action_label') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @foreach ($list as $domain)
                <tr @if($loop->even)class="bg-grey"@endif>
                    <td class="border px-4 py-2 text-center">{{ $domain->domain }}</td>
                    @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines') && empty($hide_actions))
                    <td class="border px-4 py-2 text-center whitespace-nowrap">
                        <button wire:click="editDomain({{ $domain->id }})" class="bg-gray-100 text-gray-600 px-6 rounded-full">
                            {{ __('content.edit') }}
                        </button>
                        <button wire:click="deleteDomain({{ $domain->id }})" class="bg-red-100 text-red-600 px-6 rounded-full">
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
    @endif
</div>
