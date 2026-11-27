{{--
    "I agree to …" for the legal documents the deployment provides
    (App\Support\LegalDocument). $links: link each document; $fallback:
    the text when it provides none.
--}}
@php
    $provided = \App\Support\LegalDocument::provided();
    $name = fn (string $key, string $route) => ($links ?? true)
        ? '<a target="_blank" href="' . e(route($route)) . '" class="underline text-sm text-gray-600 hover:text-gray-900">' . e(__('content.' . $key)) . '</a>'
        : e(__('content.' . $key));
    $documents = [
        'terms_of_service' => $name('terms_of_service', 'terms.show'),
        'privacy_policy' => $name('privacy_policy', 'policy.show'),
    ];
    $sentence = match (true) {
        $provided['privacy_policy'] && $provided['terms_of_service'] => __('content.i_agree_to_the_terms_of_service_and_privacy_policy', $documents),
        $provided['privacy_policy'] => __('content.i_agree_to_the_privacy_policy', $documents),
        $provided['terms_of_service'] => __('content.i_agree_to_the_terms_of_service', $documents),
        default => e($fallback ?? ''),
    };
@endphp
{!! $sentence !!}
