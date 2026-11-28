@extends('errors::minimal')

@section('title', __('content.server_error_title'))
@section('code', '500')
@section('message')
    <div class="content">
        <div class="title">{{ __('content.server_error_message') }}</div>

        {{-- No scripts: the page shows because the app failed, and loads
             nothing from other sites. The id lets support find the report. --}}
        @if(config('sentry.dsn') && app()->bound('sentry') && app('sentry')->getLastEventId())
            <div class="subtitle">{{ __('content.server_error_id', ['id' => app('sentry')->getLastEventId()]) }}</div>
            @if ($support = config('app.support_email'))
                <div class="subtitle">{!! __('content.server_error_quote', ['support' => '<a href="mailto:' . e($support) . '">' . e($support) . '</a>']) !!}</div>
            @endif
        @endif
    </div>
@endsection
