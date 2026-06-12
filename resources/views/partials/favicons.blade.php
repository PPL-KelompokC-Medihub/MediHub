@php
    $brandTitle = trim($__env->yieldContent('title', 'MediHub')) ?: 'MediHub';
    $brandDescription = 'MediHub - Jembatan Menuju Layanan Kesehatan Terbaik';
@endphp

<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="icon" href="{{ asset('favicon-32x32.png') }}" sizes="32x32" type="image/png">
<link rel="shortcut icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<meta name="theme-color" content="#0D78C3">
<meta property="og:type" content="website">
<meta property="og:site_name" content="MediHub">
<meta property="og:title" content="{{ $brandTitle }}">
<meta property="og:description" content="{{ $brandDescription }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ asset('og-image.png') }}">
<meta property="og:image:secure_url" content="{{ asset('og-image.png') }}">
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $brandTitle }}">
<meta name="twitter:description" content="{{ $brandDescription }}">
<meta name="twitter:image" content="{{ asset('og-image.png') }}">
