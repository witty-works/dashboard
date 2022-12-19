<x-app-layout>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page-subscription lg:ml-20 margin-bottom">
                <div class="ibarra-sub-title-h1 margin-top align-center">
                    {{ __('content.witty_download') }}
                </div>

                <div id="not_supported" style="display: none" class="align-center redirecting-message">
                    {!! __('content.witty_download_description_not_supported_yet') !!}
                </div>

                <div id="redirecting" style="display: none" class="align-center redirecting-message">
                    {!! __('content.witty_download_redirecting') !!}
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
                                    loginUrl = wittyIsInstalled.getAttribute('login-url')
                                    if (loginUrl) {
                                        url = @json(config('app.welcome_url'));
                                    } else {
                                        url = @json(config('app.try_out_url'));
                                    }
                                    setRedirect(url);
                                } else {
                                    url = false;
                                    if (!!window.chrome) {
                                        url = @json(config('app.browsers')['chrome']['store_href']);
                                    } else if (window.navigator.userAgent.toLowerCase().indexOf("firefox") > -1) {
                                        url = @json(config('app.browsers')['firefox']['store_href']);
                                    }

                                    if (url) {
                                        setRedirect(url);
                                    } else {
                                        const notSupported = document.querySelector('#not_supported');
                                        notSupported.style.display = 'block';
                                    }
                                }
                            });

                            function setRedirect(url)
                            {
                                const redirecting = document.querySelector('#redirecting');
                                redirecting.style.display = 'block';

                                setTimeout(function() {
                                    redirectToStore(url);
                                    },
                                    @json(config('app.browser_check_time'))
                                );
                            }

                            function redirectToStore(url)
                            {
                                if (@json(config('app.browser_redirect'))) {
                                    window.location.replace(url);
                                } else {
                                    const redirecting = document.querySelector('#redirecting');
                                    redirecting.innerText = 'redirected to .. ' + url;
                                }
                            }
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
