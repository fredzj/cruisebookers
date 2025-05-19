<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Cruisebookers')</title>
    <meta name="description"                    content="@yield('meta_description', 'CruiseBookers · Voordelige cruise vakanties in 2025/2026')">
    <meta name="viewport"                       content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification"       content="rYaL7RBa_eH1Hl4oIFCd4uOCW8uR9-OYs9zx8aULw7s" />
    <meta name="robots"                         content="@yield('robots', 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1')" />
	<meta name="twitter:card"					content="summary_large_image" />
	<meta name="twitter:description"			content="@yield('meta_description', 'CruiseBookers · Voordelige cruise vakanties in 2025/2026')">
	<meta name="twitter:image"					content="https://cruisebookers.nl/images/hero.jpg">
	<meta name="twitter:image:alt"				content="CruiseBookers">
	<meta name="twitter:site"					content="CruiseBookers.nl" />
	<meta name="twitter:title"					content="@yield('title', 'Cruisebookers')">
    <meta name="verification"                   content="d17273cf421505896976a37bd3651dd7" /> <!-- AWIN -->
	<meta name="b82df0e7a1e7d3f"                content="4f4224c6dd100bb1f881651af17967b7" /> <!-- DAISYCON -->
    <meta name="msvalidate.01"                  content="6EC4EEFC9ED10794EB0F2D1E427AEBCE" /> <!-- BING -->
    <meta property="og:description"				content="@yield('meta_description', 'CruiseBookers · Voordelige cruise vakanties in 2025/2026')" />
	<meta property="og:image"					content="https://cruisebookers.nl/images/hero.jpg" />
	<meta property="og:image:alt"				content="Cruisebookers" />
	<meta property="og:image:height"			content="1375" />
	<meta property="og:image:type"				content="image/jpeg" />
	<meta property="og:image:width"				content="1920" />
	<meta property="og:locale"					content="nl_NL" />
	<meta property="og:site_name"				content="CruiseBookers" />
	<meta property="og:title"					content="@yield('title', 'Cruisebookers')" />
	<meta property="og:type"					content="website" />
	<meta property="og:url"						content="https://cruisebookers.nl/" />
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-JZ38CT8MGP"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-JZ38CT8MGP');
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="canonical"                       href="@yield('canonical', url()->current())">
    <link rel="stylesheet"                      href="{{ asset('css/custom.css') }}">
	<link rel="icon"							href="https://cruisebookers.nl/favicon.ico">
    <script src="https://kit.fontawesome.com/89cc921f34.js" crossorigin="anonymous"></script>
	<!--! Speculation Rules Content by Harry Roberts, csswizardry.com, available under the MIT license. -->
	<script type="speculationrules">
        {
            "prefetch": [
            {
                "where": {
                "selector_matches": "[data-prefetch='']"
                },
                "eagerness": "immediate"
            },
            {
                "where": {
                "and": [
                    { "href_matches": "/*" },
                    { "not": { "selector_matches": "[data-prefetch=false]" } }
                ]
                },
                "eagerness": "moderate"
            }
            ],
            "prerender": [
            {
                "where": {
                "selector_matches": "[data-prefetch=prerender]"
                },
                "eagerness": "immediate"
            },
            {
                "where": {
                "selector_matches": "[data-prefetch='']"
                },
                "eagerness": "moderate"
            }
            ]
        }
    </script>
    <!-- GetYourGuide Analytics -->
    <!-- <script async defer src="https://widget.getyourguide.com/dist/pa.umd.production.min.js" data-gyg-partner-id="GX9K7AY"></script> -->
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark xbg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img width="40" height="40" src="{{ asset('images/logo-cruisebookers.jpg') }}" alt="Logo" style="height: 40px;">
                CruiseBookers
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('search') }}">Alle cruises</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('cruiselines') }}">Cruisemaatschappijen</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('partners') }}">Reisorganisaties</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('traveladvices') }}">Reisadviezen</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <main class="xpy-4">
        @yield('content')
    </main>

    <footer class="text-white py-4">
        <div class="container">
            <div class="row">
                <!-- Column 1 -->
                <div class="col-md-2">
                    <h5>CruiseBookers</h5>
                    <img width="196" height="196" src="{{ asset('images/logo-cruisebookers.jpg') }}" alt="Cruisebookers Logo" class="img-fluid mb-2">
                    <ul class="list-unstyled">
                        <li><a href="{{ route('privacyverklaring') }}" class="text-white">Privacyverklaring</a></li>
                        <li><a href="{{ route('cookieverklaring') }}" class="text-white">Cookieverklaring</a></li>
                        <li><a href="{{ route('disclaimer') }}" class="text-white">Disclaimer</a></li>
                        <li><a href="{{ route('sitemap') }}" class="text-white">Sitemap</a></li>
                    </ul>
                    <div class="mt-3"></div>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('cruiselines') }}" class="text-white">Cruisemaatschappijen</a></li>
                        <li><a href="{{ route('partners') }}" class="text-white">Reisorganisaties</a></li>
                        <li><a href="{{ route('traveladvices') }}" class="text-white">Reisadviezen</a></li>
                        <li><a href="/blog" class="text-white">Blog</a></li>
                    </ul>
                </div>

                <!-- Columns 2-6 -->
                <div class="col-md-2">
                    <h5>Zeecruises</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('search', ['continent' => ['Antarctica']]) }}" class="text-white">Antarctica Cruises</a></li>
                        <li><a href="{{ route('search', ['holidaytype_is_seacruise_bluecruise' => ['1']]) }}" class="text-white">Blue Cruises</a></li>
                        <li><a href="{{ route('search', ['holidaytype_is_seacruise_caribbean' => ['1']]) }}" class="text-white">Caraïbische Cruises</a></li>
                        <li><a href="{{ route('search', ['holidaytype_is_seacruise_hurtigruten' => ['1']]) }}" class="text-white">Hurtigruten</a></li>
                        <li><a href="{{ route('search', ['holidaytype_is_seacruise_mediterranean' => ['1']]) }}" class="text-white">Middellandse Zee Cruises</a></li>
                        <li><a href="{{ route('search', ['holidaytype_is_seacruise_sailing' => ['1']]) }}" class="text-white">Zeilcruises</a></li>
                        <li><a href="{{ route('search', ['holidaytype_is_seacruise_world' => ['1']]) }}" class="text-white">Wereldcruises</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h5>Riviercruises Europa</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('search', ['holidaytype_is_rivercruise_danube' => ['1']]) }}" class="text-white">Donaucruises</a></li>
                        <li><a href="{{ route('search', ['holidaytype_is_rivercruise_moselle' => ['1']]) }}" class="text-white">Moezelcruises</a></li>
                        <li><a href="{{ route('search', ['holidaytype_is_rivercruise_rhine' => ['1']]) }}" class="text-white">Rijncruises</a></li>
                    </ul>
                    <h5>Riviercruises Egypte</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('search', ['holidaytype_is_rivercruise_nile' => ['1']]) }}" class="text-white">Nijlcruises</a></li>
                    </ul>
                </div>
                <div class="col-md-2"></div>
                <div class="col-md-2"></div>
                <div class="col-md-2"></div>
            </div>
        </div>
        <div class="bg-transparent text-center py-2 mt-3">
            <p class="mb-0">&copy; {{ date('Y') }} CruiseBookers. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>