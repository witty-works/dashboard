<x-guest-layout>
    <div>
        <x-jet-authentication-card-logo />
    </div>

    <div class="w-full bg-white prose">
        @php
            $currentURL = URL::current();
            $currentURL.= strpos('?', $currentURL) === false ? '?' : '&';
        @endphp
        <a href="{{ $currentURL }}refresh=true">Refresh</a>
        <table>
            <tr>
                <th>Top Subcategories</th>
                <th>Top Words</th>
                <th>Event</th>
            </tr>
            <tr>
                <td>
                    @foreach ($topSubcategories['events'] as $event => $values)
                    <h1>{{ $event }}</h1>
                    Last refresh: {{ $topSubcategories['last_refresh'][$event] }}

                    <table>
                        <tr><th>Word</th><th>Count</th></tr>
                        @foreach ($values as $word => $count)
                        <tr><td>{{ $word }}</td><td>{{ $count }}</td></tr>
                        @endforeach
                    </table>
                    @endforeach
                </td>
                <td>
                    @foreach ($topWords['events'] as $event => $values)
                    <h1>{{ $event }} </h1>
                    Last refresh: {{ $topWords['last_refresh'][$event] }}

                    <table>
                        <tr><th>Word</th><th>Count</th></tr>
                        @foreach ($values as $word => $count)
                        <tr><td>{{ $word }}</td><td>{{ $count }}</td></tr>
                        @endforeach
                    </table>
                    @endforeach
                </td>
                <td>
                    @foreach (array_keys($dataDau['events']) as $event)
                    <h1>{{ $event }} </h1>
                    <ul>
                        <li>Last refresh DAU: {{ $dataDau['last_refresh'][$event] }}</li>
                            <li>Last refresh Total: {{ $dataTotal['last_refresh'][$event] }}</li>
                    </ul>
                    @endforeach

                    @foreach ($dataDau['days'] as $day)
                    <h1>{{ $day }} </h1>

                    <table>
                        <tr><th>Event</th><th>DAU</th><th>Total</th></tr>
                    @foreach (array_keys($dataDau['events']) as $event)
                        <tr>
                            <td>{{ $event }}</td>
                            <td>{{ $dataDau['events'][$event][$day] }}</td>
                            <td>{{ $dataTotal['events'][$event][$day] }}</td>
                        </tr>
                    @endforeach
                    </table>
                    @endforeach
                </td>
            </tr>
        </table>
    </div>
</x-guest-layout>