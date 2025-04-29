<!-- filepath: d:\Work\Clients\FredOnis\cruisebookers\resources\views\home.blade.php -->
@extends('layouts.app')

@section('title', 'Home')

@section('content')
<!-- Hero Section -->
<div class="hero mb-5">
    <img src="{{ asset('images/hero.jpg') }}" class="img-fluid w-100" alt="CruiseBookers">
    <div class="hero-text text-center">
        <h1 class="display-4 text-white">Welke cruise past bij jou?</h1>
        <div class="container my-5">
            <form action="{{ route('search') }}" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Zoek naar cruises, bestemmingen of maatschappijen..." aria-label="Search" aria-describedby="search-button">
                    <button class="btn btn-primary" type="submit" id="search-button">Zoeken</button>
                </div>
            </form>
        </div>
        <p class="lead text-white">Ontdek de mooiste cruises over zee en rivier.</p>
    </div>
</div>

<div class="container my-4">
    <!-- Primary Cruise Types -->
    <h2 class="mb-4">Onze Cruisevakanties</h2>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
        <div class="col">
            <a href="{{ route('search', ['cruiseline_category' => ['zeecruise']]) }}" class="text-decoration-none">
                <div class="card h-100">
                    <img src="{{ asset('images/zeecruise.jpg') }}" class="card-img-top" alt="Zeecruise">
                    <div class="card-body">
                        <h5 class="card-title">Zeecruises</h5>
                        <p class="card-text">Geniet van een luxe cruise over de open zee.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('search', ['cruiseline_category' => ['riviercruise']]) }}" class="text-decoration-none">
                <div class="card h-100">
                    <img src="{{ asset('images/riviercruise.jpg') }}" class="card-img-top" alt="Riviercruise">
                    <div class="card-body">
                        <h5 class="card-title">Riviercruises</h5>
                        <p class="card-text">Ontdek prachtige steden langs de mooiste rivieren.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('search', ['cruiseline_category' => ['minicruise']]) }}" class="text-decoration-none">
                <div class="card h-100">
                    <img src="{{ asset('images/minicruise.jpg') }}" class="card-img-top" alt="Minicruise">
                    <div class="card-body">
                        <h5 class="card-title">Minicruises</h5>
                        <p class="card-text">Een korte maar onvergetelijke cruise-ervaring.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Main Seacruise Types -->
    <h2 class="my-5">Populaire Zeecruises</h2>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <div class="col">
            <a href="{{ route('search', ['holidaytype_is_seacruise_mediterranean' => ['1']]) }}" class="text-decoration-none">
                <div class="card h-100">
                    <img src="{{ asset('images/zeecruise-middellandse-zee.jpg') }}" class="card-img-top" alt="Middellandse Zee Cruises">
                    <div class="card-body">
                        <h5 class="card-title">Middellandse Zee Cruises</h5>
                        <p class="card-text">Verken de prachtige kustlijnen van de Middellandse Zee.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('search', ['holidaytype_is_seacruise_caribbean' => ['1']]) }}" class="text-decoration-none">
                <div class="card h-100">
                    <img src="{{ asset('images/zeecruise-caraibische-cruises.jpg') }}" class="card-img-top" alt="Caraïbische Cruises">
                    <div class="card-body">
                        <h5 class="card-title">Caraïbische Cruises</h5>
                        <p class="card-text">Ontsnap naar tropische eilanden en turquoise wateren.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('search', ['holidaytype_is_seacruise_world' => ['1']]) }}" class="text-decoration-none">
                <div class="card h-100">
                    <img src="{{ asset('images/zeecruise-wereldcruises.jpg') }}" class="card-img-top" alt="Wereldcruises">
                    <div class="card-body">
                        <h5 class="card-title">Wereldcruises</h5>
                        <p class="card-text">Beleef een reis rond de wereld op een luxe schip.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('search', ['holidaytype_is_seacruise_sailing' => ['1']]) }}" class="text-decoration-none">
                <div class="card h-100">
                    <img src="{{ asset('images/zeecruise-zeilcruises.jpg') }}" class="card-img-top" alt="Zeilcruises">
                    <div class="card-body">
                        <h5 class="card-title">Zeilcruises</h5>
                        <p class="card-text">Ervaar de magie van zeilen op open water.</p>
                    </div>
                </a>
            </div>
        </div>
        <div class="col">
            <a href="{{ route('search', ['holidaytype_is_seacruise_bluecruise' => ['1']]) }}" class="text-decoration-none">
                <div class="card h-100">
                    <img src="{{ asset('images/zeecruise-blue-cruises.jpg') }}" class="card-img-top" alt="Blue Cruises">
                    <div class="card-body">
                        <h5 class="card-title">Blue Cruises</h5>
                        <p class="card-text">Ontdek de verborgen baaien van de Egeïsche Zee.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('search', ['holidaytype_is_seacruise_hurtigruten' => ['1']]) }}" class="text-decoration-none">
                <div class="card h-100">
                    <img src="{{ asset('images/zeecruise-hurtigruten.jpg') }}" class="card-img-top" alt="Hurtigruten Cruises">
                    <div class="card-body">
                        <h5 class="card-title">Hurtigruten Cruises</h5>
                        <p class="card-text">Verken de adembenemende fjorden van Noorwegen.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('search', ['continent' => ['Antarctica']]) }}" class="text-decoration-none">
                <div class="card h-100">
                    <img src="{{ asset('images/zeecruise-antarctica.jpg') }}" class="card-img-top" alt="Antarctica Cruises">
                    <div class="card-body">
                        <h5 class="card-title">Antarctica Cruises</h5>
                        <p class="card-text">Beleef een unieke reis naar de ijzige wereld van Antarctica.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Rivercruise Types -->
    <h2 class="my-5">Populaire Riviercruises in Europa</h2>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
        <div class="col">
            <a href="{{ route('search', ['holidaytype_is_rivercruise_rhine' => ['1']]) }}" class="text-decoration-none">
                <div class="card h-100">
                    <img src="{{ asset('images/riviercruise-rijn.jpg') }}" class="card-img-top" alt="Rijn Cruises">
                    <div class="card-body">
                        <h5 class="card-title">Rijncruises</h5>
                        <p class="card-text">Beleef de romantiek van de Rijn met zijn kastelen en wijngaarden.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('search', ['holidaytype_is_rivercruise_moselle' => ['1']]) }}" class="text-decoration-none">
                <div class="card h-100">
                    <img src="{{ asset('images/riviercruise-moezel.jpg') }}" class="card-img-top" alt="Moezel Cruises">
                    <div class="card-body">
                        <h5 class="card-title">Moezelcruises</h5>
                        <p class="card-text">Geniet van de schilderachtige Moezelvallei en haar wijngaarden.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('search', ['holidaytype_is_rivercruise_danube' => ['1']]) }}" class="text-decoration-none">
                <div class="card h-100">
                    <img src="{{ asset('images/riviercruise-donau.jpg') }}" class="card-img-top" alt="Donau Cruises">
                    <div class="card-body">
                        <h5 class="card-title">Donaucruises</h5>
                        <p class="card-text">Ontdek de historische steden langs de majestueuze Donau.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
<div class="container my-4">
    <!-- Cruiseline Logos Section -->
    <h2 class="my-5">Onze Cruisemaatschappijen</h2>
    <div class="marquee">
        <div class="marquee-content">
            @foreach($cruiselines as $cruiseline)
                <a href="{{ route('search', ['cruiseline' => [$cruiseline->name]]) }}" class="text-decoration-none">
                    <img src="{{ $cruiseline->url_logo }}" class="img-fluid mx-3" alt="{{ $cruiseline->name }}" style="max-height: 80px;">
                </a>
            @endforeach
            <!-- Duplicate the content for continuous scrolling -->
            @foreach($cruiselines as $cruiseline)
                <a href="{{ route('search', ['cruiseline' => [$cruiseline->name]]) }}" class="text-decoration-none">
                    <img src="{{ $cruiseline->url_logo }}" class="img-fluid mx-3" alt="{{ $cruiseline->name }}" style="max-height: 80px;">
                </a>
            @endforeach
        </div>
    </div>
</div>
<!--
<div class="container my-4">
    !-- Random Banner Section --
    @if(isset($randomBanner))
        <div class="random-banner text-center mb-4">
            <a href="{{ route('search', ['merchant' => [$randomBanner->merchant_slug]]) }}">
                {!! $randomBanner->banner !!}
            </a>
        </div>
    @endif
</div>
-->
@endsection