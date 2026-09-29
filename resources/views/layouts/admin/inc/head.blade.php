<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>@yield('title') — تخرُّج</title>
<link rel="icon" href="{{ asset('assets/img/takharruj-logo.svg') }}">
{{-- خطوط مستضافة محلياً بدل fonts.googleapis.com --}}
<link rel="preload" href="{{ asset('assets/fonts/IBMPlexSansArabic-400-arabic.woff2') }}" as="font" type="font/woff2" crossorigin>
<link href="{{ asset('assets/fonts/fonts.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/fonts/cairo.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/tabler/css/tabler.rtl.min.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/tabler-icons/tabler-icons.min.css') }}" rel="stylesheet">
@stack('css')
<link href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}" rel="stylesheet">
