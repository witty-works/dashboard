<x-app-layout :pagetitle="__('content.witty_download')">
    <div class="wittyworks-page-wrapper">
        <div class="wittyworks-page-subscription lg:ml-20 margin-bottom">
            <div id="not_installed_title" style="display: none" class="ibarra-sub-title-h1 margin-top align-center">
                {{ __('content.witty_download') }}
            </div>

            <div id="installed_title" style="display: none" class="ibarra-sub-title-h1 margin-top align-center">
                {{ __('content.witty_download_installed') }}
            </div>

            <div>
                <div class="py-10">
                    <div id="not_supported" style="display: none" class="align-center warning-message">
                        {!! __('content.witty_download_description_not_supported_yet') !!}
                    </div>
    
                    <div id="not_installed_description" style="display: none" class="w-full col-span-6 sm:col-span-4 margin-bottom align-center">
                        {!! __('content.witty_download_description') !!}
                    </div>

                    <div id="already_installed" style="display: none" class="w-full col-span-6 sm:col-span-4 margin-bottom align-center">
                        <h2>{!! __('content.witty_download_already_installed') !!}</h2>

                        <div class="mt-5">
                            <a id="login-witty-url" class="button primary-button-red download-button" href="https://witty.works/welcome">
                                {{ __('content.sign_in') }}
                            </a>
                        </div>
                    </div>
    
                    <div id="already_signedin" style="display: none" class="w-full col-span-6 sm:col-span-4 margin-bottom align-center">
                        <div class="mt-5">
                            <a class="button primary-button-red download-button" href="{{ route('editor', ['onboarding' => 'true']) }}">
                                {{ __('content.try_out') }}
                            </a>
                        </div>
                    </div>
    
                    <div id="store_links" style="display: none" class="h-56 grid grid-cols-3 gap-4 content-center">
                        @foreach (config('app.browsers') as $key => $browser)
                        <div class="rounded-lg bg-white browser-selection" id="{{ $key }}">
                            <a href="{{ $browser['store_href'] }}">
                                <img class="browser-logo" width="100px;" src="{{ URL::asset($browser['image_src']) }}" alt="{{ __('content.browser_name_'.$key) }}" />
                            </a>

                            <a href="{{ $browser['store_href'] }}" class="button primary-button-red download-button">{{ __('content.browser_get_'.$key) }}</a>
                        </div>
                        @endforeach
                    </div>

                    <script nonce="{{ csp_nonce('script') }}">
                        window.addEventListener('load', (event) => {
                            const wittyIsInstalled = document.querySelector('witty-is-installed');

                            if (wittyIsInstalled) {
                                const installed_title = document.querySelector('#installed_title');
                                installed_title.style.display = 'block';

                                const loginUrl = wittyIsInstalled.getAttribute('login-url')

                                if (loginUrl) {
                                    const loginWittyUrl = document.querySelector('#login-witty-url');
                                    loginWittyUrl.setAttribute('href', loginUrl + '?target=' + encodeURIComponent(@json(route('editor', ['onboarding' => 'true']))))

                                    const alreadyInstalled = document.querySelector('#already_installed');
                                    alreadyInstalled.style.display = 'block';
                                } else {
                                    const alreadySignedin = document.querySelector('#already_signedin');
                                    alreadySignedin.style.display = 'block';
                                }
                            } else {
                                const not_installed_title = document.querySelector('#not_installed_title');
                                not_installed_title.style.display = 'block';

                                const store_links = document.querySelector('#store_links');
                                store_links.style.display = 'block';

                                browser = detectBrowser();
                                if (browser) {
                                    const browser_store_links = document.querySelectorAll('.browser-selection');
                                    [].forEach.call(browser_store_links, function(browser_store_link) {
                                        if (browser_store_link.id !== browser) {
                                            browser_store_link.style.display = 'none';
                                        }
                                    });
                                } else {
                                    const not_installed_description = document.querySelector('#not_installed_description');
                                    not_installed_description.style.display = 'block';

                                    const notSupported = document.querySelector('#not_supported');
                                    notSupported.style.display = 'block';
                                }
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
