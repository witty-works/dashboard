<x-app-layout :pagetitle="__('content.witty_word_addin')">
    <div class="wittyworks-page-wrapper">
        <div class="wittyworks-page lg:ml-20">
            <h1 class="ibarra-sub-title-h1 margin-top">
                {{ __('content.witty_word_addin') }}
            </h1>

            <div class="py-10">
                @if(request()->get('status') === 'failed')
                {!! __('content.witty_word_addin_text_failed', ['support_email' => e(config('app.support_email'))]) !!}
                @elseif(request()->get('status') === 'invite_only')
                {!! __('content.witty_word_addin_text_invite_only') !!}
                @else
                {!! __('content.witty_word_addin_text') !!}
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
