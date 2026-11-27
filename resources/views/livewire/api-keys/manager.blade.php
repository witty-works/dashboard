<div x-data="{ key: null, copied: false }" x-on:api-key-created.window="key = $event.detail.key; copied = false; $nextTick(() => $refs.keyHeading.focus())">
    {{-- In the page from the start: a live region added together with its
         text is often not announced. Focus moves to the key as well. --}}
    <div role="status">
    <template x-if="key">
        <div class="mb-8 rounded border border-green-700 bg-green-50 p-4">
            <p class="font-semibold" x-ref="keyHeading" tabindex="-1">{{ __('content.api_keys_new_key') }}</p>
            <p class="text-sm">{{ __('content.api_keys_shown_once') }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <code class="break-all rounded bg-white px-2 py-1" x-text="key"></code>
                <button type="button" class="button secondary-button" x-on:click="navigator.clipboard.writeText(key); copied = true">{{ __('content.api_keys_copy') }}</button>
                <span x-show="copied" class="text-sm">{{ __('content.api_keys_copied') }}</span>
                <button type="button" class="underline text-sm" x-on:click="key = null; $nextTick(() => document.getElementById('api-key-name').focus())">{{ __('content.api_keys_done') }}</button>
            </div>
        </div>
    </template>
    </div>

    @if ($unavailable)
        <p class="mb-6 text-red-700" role="alert">{{ __('content.api_keys_unavailable') }}</p>
    @endif
    @error('keys') <p class="mb-6 text-red-700" role="alert">{{ $message }}</p> @enderror

    <h2 class="mb-2 text-lg font-semibold">{{ __('content.api_keys_limits_title') }}</h2>
    <ul class="mb-8 text-sm">
        @foreach ($teams as $team)
            <li wire:key="limit-{{ $team->id }}">
                {{ $team->name }}:
                {{ $team->llm_requests_per_minute ? __('content.api_keys_limit_value', ['count' => $team->llm_requests_per_minute]) : __('content.api_keys_no_limit') }}
            </li>
        @endforeach
    </ul>

    <h2 class="mb-2 text-lg font-semibold">{{ __('content.api_keys_create_title') }}</h2>
    <form wire:submit="create" class="mb-8 flex flex-wrap items-end gap-4">
        <div>
            <label for="api-key-name" class="block text-sm">{{ __('content.api_keys_name') }}</label>
            <input id="api-key-name" @error('name') aria-invalid="true" aria-describedby="api-key-name-error" @enderror type="text" wire:model="name" class="form-input" maxlength="100" required>
            @error('name') <p id="api-key-name-error" class="text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="api-key-team" class="block text-sm">{{ __('content.api_keys_team') }}</label>
            <select id="api-key-team" @error('teamId') aria-invalid="true" aria-describedby="api-key-team-error" @enderror wire:model="teamId" class="form-select" required>
                @if ($teams->count() !== 1)
                    <option value="">{{ __('content.api_keys_choose_team') }}</option>
                @endif
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
            @error('teamId') <p id="api-key-team-error" class="text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="api-key-expires" class="block text-sm">{{ __('content.api_keys_expires_optional') }}</label>
            <input id="api-key-expires" @error('expiresOn') aria-invalid="true" aria-describedby="api-key-expires-error" @enderror type="date" wire:model="expiresOn" class="form-input" min="{{ now()->addDay()->toDateString() }}">
            @error('expiresOn') <p id="api-key-expires-error" class="text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="button primary-button">{{ __('content.api_keys_create') }}</button>
    </form>

    <h2 class="mb-2 text-lg font-semibold">{{ __('content.api_keys_list_title') }}</h2>
    @if ($keys->isEmpty())
        <p class="text-sm text-gray-600">{{ __('content.api_keys_none') }}</p>
    @else
        <table class="min-w-full divide-y divide-gray-200 bg-white">
            <thead>
                <tr>
                    <th scope="col" class="px-4 py-2 text-left">{{ __('content.api_keys_name') }}</th>
                    <th scope="col" class="px-4 py-2 text-left">{{ __('content.api_keys_team') }}</th>
                    <th scope="col" class="px-4 py-2 text-left">{{ __('content.api_keys_created') }}</th>
                    <th scope="col" class="px-4 py-2 text-left">{{ __('content.api_keys_expires') }}</th>
                    <th scope="col" class="px-4 py-2 text-left">{{ __('content.admin_actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($keys as $key)
                    <tr wire:key="key-{{ $key['id'] }}">
                        <td class="px-4 py-2">{{ $key['name'] }}</td>
                        <td class="px-4 py-2 text-sm">{{ $teamNames[$key['team_id']] ?? __('content.api_keys_no_team') }}</td>
                        <td class="px-4 py-2 text-sm">{{ \Illuminate\Support\Carbon::parse($key['created_at'])->isoFormat('LL') }}</td>
                        <td class="px-4 py-2 text-sm">{{ $key['expires_at'] ? \Illuminate\Support\Carbon::parse($key['expires_at'])->isoFormat('LLL') : __('content.api_keys_never') }}</td>
                        <td class="px-4 py-2 text-sm">
                            @if (empty($key['replaced']))
                            <label class="sr-only" for="grace-{{ $key['id'] }}">{{ __('content.api_keys_grace') }} {{ $key['name'] }}</label>
                            <select id="grace-{{ $key['id'] }}" wire:model="grace.{{ $key['id'] }}" class="form-select text-sm">
                                <option value="86400">{{ __('content.api_keys_grace_day') }}</option>
                                <option value="604800">{{ __('content.api_keys_grace_week') }}</option>
                                <option value="0">{{ __('content.api_keys_grace_none') }}</option>
                            </select>
                            <button type="button" class="ml-2 underline" wire:click="replace(@js($key['id']))">{{ __('content.api_keys_replace') }}<span class="sr-only"> {{ $key['name'] }}</span></button>
                            @else
                            <span>{{ __('content.api_keys_replaced') }}</span>
                            @endif
                            <button type="button" class="ml-4 underline" wire:click="revoke(@js($key['id']))" wire:confirm="{{ __('content.api_keys_revoke_confirm') }}">{{ __('content.api_keys_revoke') }}<span class="sr-only"> {{ $key['name'] }}</span></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
