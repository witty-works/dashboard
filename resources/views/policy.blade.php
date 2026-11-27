<x-app-layout :pagetitle="__('content.privacy_policy')">
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-4">{{ __('content.privacy_policy') }}</h1>
        <div class="bg-white p-6 rounded shadow space-y-4">
            {{-- English only for now. The deployment's contact comes from IMPRINT_CONTACT. --}}
            <section>
                <h2 class="text-xl font-bold mb-2">Imprint</h2>
                <p>
                    Berner Fachhochschule Wirtschaft<br>
                    Institut Public Sector Transformation<br>
                    Brückenstrasse 73<br>
                    3005 Bern<br>
                    Switzerland
                </p>
                @if ($contact = config('app.imprint_contact'))
                    <p class="mt-2">Contact: <a class="underline" href="mailto:{{ $contact }}">{{ $contact }}</a></p>
                @endif
            </section>

            <section>
                <h2 class="text-xl font-bold mb-2">Privacy</h2>
                <p>
                    The texts you check are processed in memory to find their issues. The
                    language service stores neither the texts nor what it finds in them.
                </p>
                <p class="mt-2">
                    This dashboard keeps your account (your name, your email address and your
                    password as a hash), the teams you belong to, and the settings and word
                    lists you and your team make. API keys are kept by the language service
                    as HMAC digests, with their names and dates.
                </p>
                <p class="mt-2">
                    The AI suggestions and rephrasing, for users who have access to them, send
                    the text to the BFH inference service, a language model service run by the
                    Bern University of Applied Sciences. Before a text reaches the language
                    model, the service checks it for certain identifiers, such as AHV numbers,
                    IBANs, credit card numbers and API keys, and refuses texts that contain
                    them. The language model then does not process the text, the refused
                    request is not stored, and you are told that the text was refused.
                </p>
            </section>
        </div>
    </div>
</x-app-layout>
