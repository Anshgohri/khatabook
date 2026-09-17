<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" type="image/png" href="/favicon.png?v=1">
<link rel="icon" href="/favicon.ico?v=1" sizes="any">
<link rel="apple-touch-icon" href="/apple-touch-icon.png?v=1">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
