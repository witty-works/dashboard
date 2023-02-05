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
                        <div id="not_installed_description" style="display: none" class="w-full col-span-6 sm:col-span-4 margin-bottom align-center">
                            {!! __('content.witty_download_description') !!}
                        </div>

                        <div id="not_supported" style="display: none" class="align-center warning-message">
                            {!! __('content.witty_download_description_not_supported_yet') !!}
                        </div>
        
                        <div id="already_installed" style="display: none" class="w-full col-span-6 sm:col-span-4 margin-bottom align-center">
                            {!! __('content.witty_download_already_installed') !!}

                            <div class="mt-5">
                                <a id="login-witty-url" class="button primary-button-red download-button" href="https://witty.works/welcome">
                                    {{ __('content.sign_in') }}
                                </a>
                            </div>
                        </div>
        
                        <div id="already_signedin" style="display: none" class="w-full col-span-6 sm:col-span-4 margin-bottom align-center">
                            {!! __('content.witty_download_already_signedin') !!}

                            <div class="mt-5">
                                <a class="button primary-button-red download-button" href="https://www.witty.works/try-out-witty">
                                    {{ __('content.try_out') }}
                                </a>
                            </div>
                        </div>
        
                        <div id="store_links" style="display: none" class="h-56 grid grid-cols-3 gap-4 content-center">
                            @foreach (config('app.browsers') as $key => $browser)
                            <div class="rounded-lg bg-white browser-selection">
                                <a href="{{ $browser['store_href'] }}">
                                    <img class="browser-logo" width="100px;" src="{{ URL::asset($browser['image_src']) }}" alt="{{ __('content.browser_name_'.$key) }}" />
                                </a>

                                <a href="{{ $browser['store_href'] }}" class="button primary-button-red download-button">{{ __('content.browser_get_'.$key) }}</a>
                            </div>
                            @endforeach
                        </div>

                        <script>
                            window.addEventListener('load', (event) => {
                                const wittyIsInstalled = document.querySelector('witty-is-installed');

                                if (wittyIsInstalled) {
                                    const installed_title = document.querySelector('#installed_title');
                                    installed_title.style.display = 'block';

                                    const loginUrl = wittyIsInstalled.getAttribute('login-url')

                                    if (loginUrl) {
                                        const loginWittyUrl = document.querySelector('#login-witty-url');
                                        loginWittyUrl.setAttribute('href', loginUrl + '?target=' + encodeURIComponent('https://www.witty.works/try-out-witty'))

                                        const alreadyInstalled = document.querySelector('#already_installed');
                                        alreadyInstalled.style.display = 'block';
                                    } else {
                                        const alreadySignedin = document.querySelector('#already_signedin');
                                        alreadySignedin.style.display = 'block';
                                    }
                                } else {
                                    const not_installed_title = document.querySelector('#not_installed_title');
                                    not_installed_title.style.display = 'block';

                                    const not_installed_description = document.querySelector('#not_installed_description');
                                    not_installed_description.style.display = 'block';

                                    const store_links = document.querySelector('#store_links');
                                    store_links.style.display = 'block';

                                    if (!window.chrome && window.navigator.userAgent.toLowerCase().indexOf("firefox") == -1) {
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
    </div>
</x-app-layout>
