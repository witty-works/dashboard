<x-app-layout>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page-subscription lg:ml-20 margin-bottom">
                <div class="ibarra-sub-title-h1 margin-top align-center">
                    {{ __('content.witty_download') }}
                </div>

                <div>
                    <div class="py-10">
                        <div class="w-full col-span-6 sm:col-span-4 margin-bottom align-center">
                            {!! __('content.witty_download_description') !!}
                        </div>

                        <div class="h-56 grid grid-cols-3 gap-4 content-center">
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
                                    const loginUrl = wittyIsInstalled.getAttribute('login-url')

                                    if (loginUrl) {
                                        const alreadyInstalled = document.querySelector('#already_installed');
                                        alreadyInstalled.style.display = 'block';
                                    } else {
                                        const alreadySignedin = document.querySelector('#already_signedin');
                                        alreadySignedin.style.display = 'block';
                                    }
                                } else if (!!window.chrome || window.navigator.userAgent.toLowerCase().indexOf("firefox") > -1) {
                                    const supported = document.querySelector('#supported');
                                    supported.style.display = 'block';
                                } else {
                                    const notSupported = document.querySelector('#not_supported');
                                    notSupported.style.display = 'block';
                                }
                            });
                            </script>
                    </div>
                </div>

                <div id="supported" style="display: none" class="align-center warning-message">
                    {!! __('content.witty_download_description_supported') !!}
                </div>

                <div id="not_supported" style="display: none" class="align-center warning-message">
                    {!! __('content.witty_download_description_not_supported_yet') !!}
                </div>

                <div id="already_installed" style="display: none" class="align-center warning-message">
                    {!! __('content.witty_download_already_installed') !!}
                </div>

                <div id="already_signedin" style="display: none" class="align-center warning-message">
                    {!! __('content.witty_download_already_signedin') !!}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
