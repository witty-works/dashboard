@extends('errors::minimal')

@section('title', __('Server Error'))
@section('code', '500')
@section('message')
    <div class="content">
        <div class="title">Something went wrong.</div>

        {{-- No scripts: the page shows because the app failed, and loads
             nothing from other sites. The id lets support find the report. --}}
        @if(config('sentry.dsn') && app()->bound('sentry') && app('sentry')->getLastEventId())
            <div class="subtitle">Error ID: {{ app('sentry')->getLastEventId() }}</div>
            @if ($support = config('app.support_email'))
                <div class="subtitle">Please quote it to <a href="mailto:{{ $support }}">{{ $support }}</a>.</div>
            @endif
        @endif
    </div>
@endsection
