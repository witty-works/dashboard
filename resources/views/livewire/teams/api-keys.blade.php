<section class="mt-10" aria-labelledby="team-api-keys-title">
    <h2 id="team-api-keys-title" class="mb-2 text-lg font-semibold">{{ __('content.api_keys_team_title') }}</h2>
    <p class="mb-4 text-sm">{{ __('content.api_keys_team_intro') }}</p>
    <p class="mb-4 text-sm">
        {{ $team->llm_requests_per_minute ? __('content.api_keys_limit_value', ['count' => $team->llm_requests_per_minute]) : __('content.api_keys_no_limit') }}
    </p>

    @if ($unavailable)
        <p class="mb-4 text-red-700" role="alert">{{ __('content.api_keys_unavailable') }}</p>
    @endif
    @error('keys') <p class="mb-4 text-red-700" role="alert">{{ $message }}</p> @enderror

    @if ($keys->isEmpty())
        <p class="text-sm text-gray-600">{{ __('content.api_keys_none') }}</p>
    @else
        <table class="min-w-full divide-y divide-gray-200 bg-white">
            <thead>
                <tr>
                    <th scope="col" class="px-4 py-2 text-left">{{ __('content.api_keys_owner') }}</th>
                    <th scope="col" class="px-4 py-2 text-left">{{ __('content.api_keys_name') }}</th>
                    <th scope="col" class="px-4 py-2 text-left">{{ __('content.api_keys_created') }}</th>
                    <th scope="col" class="px-4 py-2 text-left">{{ __('content.api_keys_expires') }}</th>
                    <th scope="col" class="px-4 py-2 text-left">{{ __('content.admin_actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($keys as $key)
                    <tr wire:key="team-key-{{ $key['id'] }}">
                        <td class="px-4 py-2 text-sm">{{ $key['email'] }}</td>
                        <td class="px-4 py-2">{{ $key['name'] }}</td>
                        <td class="px-4 py-2 text-sm">{{ \Illuminate\Support\Carbon::parse($key['created_at'])->isoFormat('LL') }}</td>
                        <td class="px-4 py-2 text-sm">{{ $key['expires_at'] ? \Illuminate\Support\Carbon::parse($key['expires_at'])->isoFormat('LLL') : __('content.api_keys_never') }}</td>
                        <td class="px-4 py-2 text-sm">
                            <button type="button" class="underline" wire:click="revoke(@js($key['id']))" wire:confirm="{{ __('content.api_keys_revoke_confirm') }}">{{ __('content.api_keys_revoke') }} <span class="sr-only">{{ $key['name'] }} ({{ $key['email'] }})</span></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>
