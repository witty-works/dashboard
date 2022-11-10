<x-jet-form-section submit="updateLicenses">
    <x-slot name="title">
        {{ __('teams.plan_summary') }}
    </x-slot>

    <x-slot name="description"></x-slot>

    <x-slot name="form">
        <x-jet-label class="lato-small-paragraph-title-h4" for="name" value="{{ __('teams.plan_name') }}" />
          <div class="lato-small-text-p margin-bottom">
              {{ $team->subscribed() ? $team->subscription()->planName() : __('stripe.witty_free') }}
          </div>

          @if($team->subscribed())
            @if($team->subscription()->ends_at)
                <div class="lato-small-text-p margin-bottom">
                    {{ __('teams.end_date') }} {{ $team->subscription()->ends_at->toFormattedDateString() }}.
                </div>
            @elseif($team->subscription()->renews_at)
                <div class="lato-small-text-p margin-bottom red">
                    {{ __('teams.renewal_date') }} {{ $team->subscription()->renews_at->toFormattedDateString() }}.
                </div>
            @endif
          @endif


          <x-jet-label for="name" class="lato-small-paragraph-title-h4 mt-4" value="{{ __('teams.team_owner') }}" />
          <div class="lato-small-text-p margin-bottom">
              {{ $team->owner->name }} (<a href="mailto:{{ $team->owner->email }}">{{ $team->owner->email }}</a>)
          </div>


          <div class="lato-small-paragraph-title-h4 mt-4">
              {{ __('teams.what_is_included') }}
          </div>

          <ul>
            <li>
              {{ __('teams.total_of_max_used_licenses', ['total' => $team->getTotalUserWithInvitationsCount(), 'max_count' => $team->getUserLicensesCount()]) }}
              @if($team->subscribed() && $team->subscription()->isPaidByInvoice())
                  <div>
                      {!! __('teams.more_licenses') !!}
                  </div>
              @endif
            </li>
            <li>
              {{ __('teams.total_of_max_used_dictionary', ['total' => $team->getTotalTermReplacementsCount(), 'max_count' => $team->getTermReplacementsCount()]) }}
            </li>
            <li>
              {{ __('teams.total_of_max_used_ignored', ['total' => $team->getTotalFalsePositivesCount(), 'max_count' => $team->getFalsePositivesCount()]) }}
            </li>
          </ul>


        @if(!Auth::user()->ownsTeam($team))
            <div class="lato-small-paragraph-title-h4 mt-4">
              {{ __('teams.license_count_label') }}
            </div>
          <div class="lato-small-text-p margin-bottom red">
            {{ __('teams.to_upgrade_contact_owner') }}
          </div>
        @endif


        @if(Auth::user()->ownsTeam($team))
        <x-jet-label for="license_count" class="lato-small-paragraph-title-h4 mt-4" value="{{ __('teams.license_count_label') }}" />
        <div class="wittyworks-license-dropdown-container  margin-bottom">
            <x-select id="license_count"
                :options="$licenseOptions"
                wire:model.defer="licenseCount"
                class="wittyworks-margin-right"
            />

            <x-jet-button>
                {{ $team->subscribed() ? ($team->subscription()->canceled() ? __('content.renew') : __('content.save')) : __('teams.upgrade') }}
            </x-jet-button>

            <x-jet-action-message class="inline-block" on="saved">
                {{ __('content.saved') }}
            </x-jet-action-message>

            <x-jet-input-error for="license_count" class="ml-2" />
        </div>

        @if($team->subscribed())
        <div class="lato-small-text-p margin-bottom">
            <a class="button primary-button-purple" href="{{ route('stripe.portal') }}">
                {{ $team->subscription()->isPaidByInvoice() ? __('stripe.contact_sales') : __('stripe.billing') }}
            </a>
        </div>
        @endif

        @endif
     </x-slot>
</x-jet-form-section>