<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon (PNG) -->
    <link rel="icon" type="image/png" href="{{ asset('storage/Recursos/Logo.png') }}" />

    <!-- Otros enlaces y scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBlGbfbX9NLvYPIp_uVEh4rFuIOROQgk8c&libraries=places"></script>

</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
