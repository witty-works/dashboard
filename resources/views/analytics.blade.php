<x-guest-layout>
    <div class="pt-4 bg-gray-100">
        <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
            <div>
                <x-jet-authentication-card-logo />
            </div>

            <div class="w-full sm:max-w-2xl mt-6 p-6 bg-white shadow-md overflow-hidden sm:rounded-lg prose">
                @foreach ($data as $event => $results)
                    <h1>{{ $event }} </h1>
                    <table>
                        <tr><th>Day</th><th>Value</th></tr>
                    @foreach ($results as $day => $value)
                        <tr><td>{{ $day }}</td><td>{{ $value}}</td></tr>
                    @endforeach
                    </table>
                @endforeach
            </div>
        </div>
    </div>
</x-guest-layout>
