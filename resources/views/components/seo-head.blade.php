@props(['title' => null, 'description' => null, 'image' => null, 'schema' => []])
@php
    $seo = \App\Support\Seo::meta($title, $description, $image, $schema);
@endphp
<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<meta name="robots" content="{{ $seo['robots'] }}">
<link rel="canonical" href="{{ $seo['canonical'] }}">
<meta property="og:type" content="{{ $seo['type'] }}">
<meta property="og:site_name" content="{{ $seo['site'] }}">
<meta property="og:locale" content="id_ID">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
<meta property="og:image" content="{{ $seo['image'] }}">
<meta property="og:image:alt" content="{{ $seo['site'] }}: sewa perahu trip 3 gili Sekotong">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
<meta name="twitter:image" content="{{ $seo['image'] }}">
@if ($seo['google'])<meta name="google-site-verification" content="{{ $seo['google'] }}">@endif
@if ($seo['bing'])<meta name="msvalidate.01" content="{{ $seo['bing'] }}">@endif
@foreach ($seo['schema'] as $block)
<script type="application/ld+json">{!! \App\Support\Seo::json($block) !!}</script>
@endforeach
@if ($seo['ga4'])
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $seo['ga4'] }}"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config',@js($seo['ga4']));</script>
@endif
