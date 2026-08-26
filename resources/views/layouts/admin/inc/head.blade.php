<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>@yield('title') — تخرُّج</title>
<link rel="icon" href="{{ asset('assets/img/takharruj-logo.svg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="{{ asset('vendor/fonts/cairo.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/tabler/css/tabler.rtl.min.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/tabler-icons/tabler-icons.min.css') }}" rel="stylesheet">
@stack('css')
<link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
