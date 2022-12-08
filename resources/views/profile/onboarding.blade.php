<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page-subscription lg:ml-20 margin-bottom">
                <div class="ibarra-sub-title-h1 margin-top">
                    {{ __('content.onboarding') }}
                </div>
                
                <div>
                    <div class="py-10">
                        <form submit="{{ route('profile.onboarding.store') }}" method="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                            <x-jet-section-title>
                                <x-slot name="title">
                                    {{ __('content.small_onboarding_survey') }}
                                </x-slot>
                            
                                <x-slot name="description">
                                </x-slot>
                            </x-jet-section-title>
                        
                            <div class="container border-radius-top">
                                <div class="w-full col-span-6 sm:col-span-4">
                                    <x-jet-label for="role" value="{!! __('content.role') !!}" />
                        
                                    <x-select name="role"
                                        :options="\App\Models\User::ROLES"
                                        class="mt-1 block w-full"
                                        selected="{{ old('role') }}"
                                    />
                                    
                                    <x-jet-input-error for="role" class="mt-2" />
                                </div>

                                <div class="w-full col-span-6 sm:col-span-4">
                                    <x-jet-label for="languages" value="{!! __('content.languages') !!}" />
                        
                                    <x-select name="languages"
                                        :options="\App\Models\TermReplacement::LANGUAGE_CODES"
                                        class="mt-1 block w-full"
                                        selected="{{ old('languages') }}"
                                    />
                        
                                    <x-jet-input-error for="languages" class="mt-2" />
                                </div>

                                @if($user->isSharedEmailAccount())
                                <div class="w-full col-span-6 sm:col-span-4">
                                    <x-jet-label for="company_name" value="{!! __('content.company_name') !!}" />
                        
                                    <x-jet-input name="company_name"
                                        type="textarea"
                                        class="mt-1 block w-full textarea-as-input"
                                        value="{{ old('company_name') }}"
                                    />
                        
                                    <x-jet-input-error for="company_name" class="mt-2" />
                                </div>
                                @endif
                            </div>
                            <div class="container light-grey-background border-radius-bottom">
                                <x-jet-button>
                                    {{ __('content.save') }}
                                </x-jet-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
