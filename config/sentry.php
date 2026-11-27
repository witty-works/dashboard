<?php

return [

    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    // The version the image was built from (its tag, APP_VERSION), else its
    // commit (GIT_REVISION); SENTRY_RELEASE overrides both. Empty values
    // count as unset, as the image sets both, empty outside a release build.
    'release' => env('SENTRY_RELEASE') ?: (env('APP_VERSION') ?: (env('GIT_REVISION') ?: null)),

    // When left empty or `null` the Laravel environment will be used
    'environment' => env('SENTRY_ENVIRONMENT'),

    // No personal data in breadcrumbs: sessions and the queue live in the
    // database, so query values carry addresses, password hashes and job
    // payloads, and log lines carry names and addresses.
    'breadcrumbs' => [
        // Capture Laravel logs in breadcrumbs
        'logs' => false,

        // Capture SQL queries in breadcrumbs
        'sql_queries' => true,

        // Capture bindings on SQL queries logged in breadcrumbs
        'sql_bindings' => false,

        // Capture queue job information in breadcrumbs
        'queue_info' => true,

        // Capture command information in breadcrumbs
        'command_info' => true,
    ],

    'tracing' => [
        // Trace queue jobs as their own transactions
        'queue_job_transactions' => env('SENTRY_TRACE_QUEUE_ENABLED', false),

        // Capture queue jobs as spans when executed on the sync driver
        'queue_jobs' => true,

        // Capture SQL queries as spans
        'sql_queries' => true,

        // Try to find out where the SQL query originated from and add it to the query spans
        'sql_origin' => true,

        // Capture views as spans
        'views' => true,

        // Indicates if the tracing integrations supplied by Sentry should be loaded
        'default_integrations' => true,
    ],

    // @see: https://docs.sentry.io/platforms/php/configuration/options/#send-default-pii
    'send_default_pii' => false,

    // No request bodies in events: a Livewire request can carry a form's
    // state, and some forms hold credentials.
    'max_request_body_size' => 'never',

    'traces_sample_rate' => (float)(env('SENTRY_TRACES_SAMPLE_RATE', 0.0)),

    'controllers_base_namespace' => env('SENTRY_CONTROLLERS_BASE_NAMESPACE', 'App\\Http\\Controllers'),

];
