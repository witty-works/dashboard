<?php

return [

    /*
     * Server
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Server
     *
     * Note: when server is empty string, it will not add to response header
     */

    'server' => '',

    /*
     * X-Content-Type-Options
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options
     *
     * Available Value: 'nosniff'
     */

    'x-content-type-options' => 'nosniff',

    /*
     * X-Download-Options
     *
     * Reference: https://msdn.microsoft.com/en-us/library/jj542450(v=vs.85).aspx
     *
     * Available Value: 'noopen'
     */

    'x-download-options' => 'noopen',

    /*
     * X-Frame-Options
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options
     *
     * Available Value: 'deny', 'sameorigin', 'allow-from <uri>'
     */

    'x-frame-options' => 'sameorigin',

    /*
     * X-Permitted-Cross-Domain-Policies
     *
     * Reference: https://www.adobe.com/devnet/adobe-media-server/articles/cross-domain-xml-for-streaming.html
     *
     * Available Value: 'all', 'none', 'master-only', 'by-content-type', 'by-ftp-filename'
     */

    'x-permitted-cross-domain-policies' => 'none',

    /*
     * X-Powered-By
     *
     * Note: it will not add to response header if the value is empty string.
     *
     * Also, verify that expose_php is turned Off in php.ini.
     * Otherwise the header will still be included in the response.
     *
     * Reference: https://github.com/bepsvpt/secure-headers/issues/58#issuecomment-782332442
     */

    'x-powered-by' => '',

    /*
     * X-XSS-Protection
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-XSS-Protection
     *
     * Available Value: '1', '0', '1; mode=block'
     */

    'x-xss-protection' => '1; mode=block',

    /*
     * Referrer-Policy
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy
     *
     * Available Value: 'no-referrer', 'no-referrer-when-downgrade', 'origin', 'origin-when-cross-origin',
     *                  'same-origin', 'strict-origin', 'strict-origin-when-cross-origin', 'unsafe-url'
     */

    'referrer-policy' => 'no-referrer',

    /*
     * Cross-Origin-Embedder-Policy
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cross-Origin-Embedder-Policy
     *
     * Available Value: 'unsafe-none', 'require-corp'
     */
    'cross-origin-embedder-policy' => 'unsafe-none',

    /*
     * Cross-Origin-Opener-Policy
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cross-Origin-Opener-Policy
     *
     * Available Value: 'unsafe-none', 'same-origin-allow-popups', 'same-origin'
     */
    'cross-origin-opener-policy' => 'unsafe-none',

    /*
     * Cross-Origin-Resource-Policy
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cross-Origin-Resource-Policy
     *
     * Available Value: 'same-site', 'same-origin', 'cross-origin'
     */
    'cross-origin-resource-policy' => 'cross-origin',

    /*
     * Clear-Site-Data
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Clear-Site-Data
     */

    'clear-site-data' => [
        'enable' => false,

        'all' => false,

        'cache' => true,

        'cookies' => true,

        'storage' => true,

        'executionContexts' => true,
    ],

    /*
     * HTTP Strict Transport Security
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/Security/HTTP_strict_transport_security
     *
     * Please ensure your website had set up ssl/tls before enable hsts.
     */

    'hsts' => [
        'enable' => false,

        'max-age' => 31536000,

        'include-sub-domains' => false,

        'preload' => false,
    ],

    /*
     * Expect-CT
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expect-CT
     */

    'expect-ct' => [
        'enable' => false,

        'max-age' => 2147483648,

        'enforce' => false,

        // report uri must be absolute-URI
        'report-uri' => null,
    ],

    /*
     * Permissions Policy
     *
     * Reference: https://w3c.github.io/webappsec-permissions-policy/
     */

    'permissions-policy' => [
        'enable' => true,

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/accelerometer

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/ambient-light-sensor

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/autoplay

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/battery

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/camera

        // https://www.chromestatus.com/feature/5690888397258752
        'cross-origin-isolated' => [
            'none' => false,

            '*' => false,

            'self' => true,

            'origins' => [],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/display-capture
        'display-capture' => [
            'none' => false,

            '*' => false,

            'self' => true,

            'origins' => [],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/document-domain

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/encrypted-media
        'encrypted-media' => [
            'none' => false,

            '*' => false,

            'self' => true,

            'origins' => [],
        ],

        // https://wicg.github.io/page-lifecycle/#execution-while-not-rendered

        // https://wicg.github.io/page-lifecycle/#execution-while-out-of-viewport

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/fullscreen
        'fullscreen' => [
            'none' => false,

            '*' => false,

            'self' => true,

            'origins' => [],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/geolocation
        'geolocation' => [
            'none' => false,

            '*' => false,

            'self' => true,

            'origins' => [],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/gyroscope
        'gyroscope' => [
            'none' => false,

            '*' => false,

            'self' => true,

            'origins' => [],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/magnetometer
        'magnetometer' => [
            'none' => false,

            '*' => false,

            'self' => true,

            'origins' => [],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/microphone
        'microphone' => [
            'none' => false,

            '*' => false,

            'self' => true,

            'origins' => [],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/midi
        'midi' => [
            'none' => false,

            '*' => false,

            'self' => true,

            'origins' => [],
        ],

        // https://drafts.csswg.org/css-nav-1/

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/payment
        'payment' => [
            'none' => false,

            '*' => false,

            'self' => true,

            'origins' => [],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/picture-in-picture
        'picture-in-picture' => [
            'none' => false,

            '*' => true,

            'self' => false,

            'origins' => [],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/publickey-credentials-get
        'publickey-credentials-get' => [
            'none' => false,

            '*' => false,

            'self' => true,

            'origins' => [],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/screen-wake-lock
        'screen-wake-lock' => [
            'none' => false,

            '*' => false,

            'self' => true,

            'origins' => [],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/sync-xhr
        'sync-xhr' => [
            'none' => false,

            '*' => true,

            'self' => false,

            'origins' => [],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/usb
        'usb' => [
            'none' => false,

            '*' => false,

            'self' => true,

            'origins' => [],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/web-share
        'web-share' => [
            'none' => false,

            '*' => false,

            'self' => true,

            'origins' => [],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy/xr-spatial-tracking
        'xr-spatial-tracking' => [
            'none' => false,

            '*' => false,

            'self' => true,

            'origins' => [],
        ],
    ],

    /*
     * Content Security Policy
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP
     */

    'csp' => [
        'enable' => true,

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy-Report-Only
        'report-only' => false,

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/report-to
        'report-to' => '',

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/report-uri
        'report-uri' => [
            // uri
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/block-all-mixed-content
        'block-all-mixed-content' => false,

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/upgrade-insecure-requests
        'upgrade-insecure-requests' => false,

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/base-uri
        'base-uri' => [
            'self' => true,
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/child-src
        'child-src' => [
            'allow' => [
                '*.hsforms.net',
                '*.hsforms.com',
            ]
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/connect-src
        'connect-src' => [
            'allow' => [
                '*.hubapi.com',
                'js.hscta.net',
                'js-eu1.hscta.net',
                '*.hubspot.com',
                '*.hs-banner.com',
                '*.hscollectedforms.net',
                '*.helphero.co',
                'www.googletagmanager.com',
                '*.google-analytics.com',
                '*.analytics.google.com',
                '*.g.doubleclick.net',
                'td.doubleclick.net',
                '*.google.com',
                '*.posthog.com',
            ]
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/default-src
        'default-src' => [
            //
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/font-src
        'font-src' => [
            'allow' => []
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/form-action
        'form-action' => [
            'fonts.gstatic.com',
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/frame-ancestors
        'frame-ancestors' => [
            //
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/frame-src
        'frame-src' => [
            'self' => true,
            'allow' => [
                '*.hubspot.com',
                '*.hs-sites.com',
                '*.hs-sites-eu1.com',
                '*.hubspotusercontent00.net',
                '*.hubspotusercontent10.net',
                '*.hubspotusercontent20.net',
                '*.hubspotusercontent30.net',
                '*.hubspotusercontent40.net',
                '*.hubspot.net',
                'play.hubspotvideo.com',
                'play-eu1.hubspotvideo.com',
                'wwww.witty.works',
                '*.hsforms.net',
                '*.hsforms.com',
                'app.helphero.co',
                'helphero.co',
                '*.posthog.com',
                'td.doubleclick.net',
                '*.g.doubleclick.net',
            ]
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/img-src
        'img-src' => [
            'self' => true,

            'schemes' => [
                'data:',
                'https:',
            ],

            'allow' => [
                'www.witty.works',
                'js.hscta.net',
                'js-eu1.hscta.net',
                'no-cache.hubspot.com',
                '*.hubspot.com',
                '*.hubspotusercontent00.net',
                '*.hubspotusercontent10.net',
                '*.hubspotusercontent20.net',
                '*.hubspotusercontent30.net',
                '*.hubspotusercontent40.net',
                '*.hubspot.net',
                'cdn2.hubspot.net',
                '*.hsforms.net',
                '*.hsforms.com',
                'www.googletagmanager.com',
                'tagmanager.google.com',
                'ssl.gstatic.com',
                'www.gstatic.com',
                '*.google-analytics.com',
                '*.analytics.google.com',
                '*.g.doubleclick.net',
                'td.doubleclick.net',
                '*.google.com',
            ],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/manifest-src
        'manifest-src' => [
            //
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/media-src
        'media-src' => [
            //
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/navigate-to
        'navigate-to' => [
            'unsafe-allow-redirects' => false,
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/object-src
        'object-src' => [
            'none' => true,
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/plugin-types
        'plugin-types' => [
            // 'application/pdf',
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/prefetch-src
        'prefetch-src' => [
            //
        ],

        // https://w3c.github.io/webappsec-trusted-types/dist/spec/#integration-with-content-security-policy
        'require-trusted-types-for' => [
            'script' => false,
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/sandbox
        'sandbox' => [
            'enable' => false,

            'allow-downloads-without-user-activation' => false,

            'allow-forms' => false,

            'allow-modals' => false,

            'allow-orientation-lock' => false,

            'allow-pointer-lock' => false,

            'allow-popups' => false,

            'allow-popups-to-escape-sandbox' => false,

            'allow-presentation' => false,

            'allow-same-origin' => false,

            'allow-scripts' => false,

            'allow-storage-access-by-user-activation' => false,

            'allow-top-navigation' => false,

            'allow-top-navigation-by-user-activation' => false,
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src
        'script-src' => [
            'self' => true,
            // @TODO remove once https://github.com/livewire/livewire/discussions/6113 is resolved
            'unsafe-eval' => true,
            'allow' => [
                'cdnjs.cloudflare.com',
                'cdn.jsdelivr.net',
                'browser.sentry-cdn.com',
                'www.googletagmanager.com',
                'tagmanager.google.com',
                'app.helphero.co',
                'helphero.co',
                'js-eu1.hs-scripts.com',
                '*.posthog.com',
                'js.hscta.net',
                'js-eu1.hscta.net',
                '*.hubspot.com',
                'static.hsappstatic.net',
                '*.usemessages.com',
                '*.hs-banner.com',
                '*.hubspotusercontent00.net',
                '*.hubspotusercontent10.net',
                '*.hubspotusercontent20.net',
                '*.hubspotusercontent30.net',
                '*.hubspotusercontent40.net',
                '*.hubspot.net',
                'www.witty.works',
                '*.hscollectedforms.net',
                '*.hsleadflows.net',
                '*.hsforms.net',
                '*.hsforms.com',
                '*.hs-scripts.com',
                '*.hubspotfeedback.com',
                'feedback.hubapi.com',
                'feedback-eu1.hubapi.com',
            ],
            'schemes' => [
                'https:',
            ],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src-attr
        'script-src-attr' => [
            //
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src-elem
        'script-src-elem' => [
            //
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/style-src
        'style-src' => [
            'self' => true,
            'unsafe-inline' => true,
            'allow' => [
                '*.posthog.com',
                '*.hubspotusercontent00.net',
                '*.hubspotusercontent10.net',
                '*.hubspotusercontent20.net',
                '*.hubspotusercontent30.net',
                '*.hubspotusercontent40.net',
                'cdn2.hubspot.net',
                'www.witty.works',
                'googletagmanager.com',
                'tagmanager.google.com',
                'fonts.googleapis.com',
            ]
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/style-src-attr
        'style-src-attr' => [
            //
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/style-src-elem
        'style-src-elem' => [
            //
        ],

        // https://w3c.github.io/webappsec-trusted-types/dist/spec/#trusted-types-csp-directive
        'trusted-types' => [
            'enable' => false,

            'allow-duplicates' => false,

            'default' => false,

            'policies' => [
                //
            ],
        ],

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/worker-src
        'worker-src' => [
            //
        ],
    ],
];
