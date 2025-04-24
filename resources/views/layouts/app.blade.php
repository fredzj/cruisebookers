<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cruisebookers')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <script src="https://kit.fontawesome.com/89cc921f34.js" crossorigin="anonymous"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark xbg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('images/icon-192.png') }}" alt="Logo" style="height: 40px;">
                Cruisebookers
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
    
    <main class="py-4">
        @yield('content')
    </main>

    <footer class="text-white py-4">
        <div class="container">
            <div class="row">
                <!-- Column 1 -->
                <div class="col-md-2">
                    <h5>Cruisebookers</h5>
                    <img src="{{ asset('images/icon-192.png') }}" alt="Cruisebookers Logo" class="img-fluid mb-2">
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
            <p class="mb-0">&copy; {{ date('Y') }} Cruisebookers. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>