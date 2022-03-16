<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ibarra+Real+Nova&family=Lato:wght@100;400;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js" integrity="sha512-rmZcZsyhe0/MAjquhTgiUcb4d9knaFc7b5xAfju483gbEXTkeJRUMIPk6s3ySZMYUHEcjKbjLjyddGWMrNEvZg==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.20/lodash.min.js" integrity="sha512-90vH1Z83AJY9DmlWa8WkjkV79yfS2n2Oxhsi2dZbIv0nC4E6m5AbH8Nh156kkM7JePmqD6tcZsfad1ueoaovww==" crossorigin="anonymous"></script>

    <!-- Styles -->
    <style>
        {!! file_get_contents($cssPath) !!}
    </style>

    @if (strpos(config('spark.brand.color'), '#') === 0)
    <style>
        .bg-custom-hex {
            background-color: {!! config('spark.brand.color') !!};
        }
    </style>
    @endif

    @include('partials/sentry')
    <script src="{{ mix('js/app.js') }}" defer></script>
    @include('partials/posthog')

    <style>
        .font-sans {
            font-family: 'Lato', sans-serif;
        }
    </style>
</head>
<body class="font-sans antialiased">
    @inertia

    <!-- Scripts -->
    <script>
        window.translations = <?php echo $translations; ?>;

        {!! file_get_contents($jsPath) !!}
    </script>
</body>
</html>
