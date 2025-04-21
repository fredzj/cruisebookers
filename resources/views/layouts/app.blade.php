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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
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
                    <li class="nav-item"><a class="nav-link" href="{{ route('sitemap') }}">Sitemap</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <main class="py-4">
        @yield('content')
    </main>

    <footer class="bg-dark text-white py-4">
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
                    </ul>
                    <div class="mt-3"></div>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('cruiselines') }}" class="text-white">Cruisemaatschappijen</a></li>
                        <li><a href="{{ route('partners') }}" class="text-white">Reisorganisaties</a></li>
                        <li><a href="{{ route('traveladvices') }}" class="text-white">Reisadviezen</a></li>
                    </ul>
                </div>

                <!-- Columns 2-6 -->
                <div class="col-md-2"></div>
                <div class="col-md-2"></div>
                <div class="col-md-2"></div>
                <div class="col-md-2"></div>
                <div class="col-md-2"></div>
            </div>
        </div>
        <div class="bg-secondary text-center py-2 mt-3">
            <p class="mb-0">&copy; {{ date('Y') }} Cruisebookers. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>