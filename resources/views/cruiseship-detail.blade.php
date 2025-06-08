@extends('layouts.app')

@section('title', $ship->name)
@section('meta_description', Str::limit($ship->description ?? '', 155))

@section('content')
<div class="container my-4">
    <div class="row">
        <div class="col-md-6">

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
              <ol class="breadcrumb">
                <li class="breadcrumb-item">
                  <a href="{{ route('home') }}">Home</a>
                </li>
                <li class="breadcrumb-item">
                  <a href="{{ route('cruiselines') }}">Cruisemaatschappijen</a>
                </li>
                <li class="breadcrumb-item">
                  <a href="{{ route('cruiselines.show', ['slug' => $cruiseline->slug]) }}">
                    {{ $cruiseline->name }}
                  </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                  {{ $ship->name }}
                </li>
              </ol>
            </nav>

            <h1>{{ $ship->name }}</h1>
            <p>{{ $ship->description }}</p>

            @php
                // Build expected image path: public/images/cruiselines/{cruiseline-slug}/{ship-slug}.jpg
                $imageFile = public_path("images/cruiseships/{$cruiseline->slug}-{$ship->slug}.jpg");
            @endphp

            @if(file_exists($imageFile))
                <img 
                    src="{{ asset("images/cruiseships/{$cruiseline->slug}-{$ship->slug}.jpg") }}" 
                    class="img-fluid mb-3" 
                    alt="{{ $ship->name }}"
                >
            @endif
            @if($ship->cruiseline)
                <p>
                    <strong>Cruisemaatschappij:</strong>
                    <a href="{{ route('cruiselines.show', ['slug' => $cruiseline->slug]) }}">
                        {{ $cruiseline->name }}
                    </a>
                </p>
            @endif
            <ul class="list-unstyled">
                @if($ship->year_built)
                    <li><strong>Bouwjaar:</strong> {{ $ship->year_built }}</li>
                @endif
                @if($ship->gross_tonnage)
                    <li><strong>Gross Tonnage:</strong> {{ $ship->gross_tonnage }}</li>
                @endif
                @if($ship->passenger_capacity)
                    <li><strong>Passenger Capacity:</strong> {{ $ship->passenger_capacity }}</li>
                @endif
                {{-- add more fields as needed --}}
            </ul>
            <!-- Add the search button -->
            <a href="{{ route('search', [
                    'cruiseline' => [$cruiseline->name],
                    'cruiseship' => [$ship->name]
                ]) }}"
                class="btn btn-primary mt-4">
                Bekijk cruises met de {{ $ship->name }}
            </a>
        </div>

        <div class="col-md-6">
            @if($ship->paragraph_destinations)
                <h2>Bestemmingen</h2>
                {!! '<p>' . str_replace("\r\n", '</p><p>', e($ship->paragraph_destinations)) . '</p>' !!}
            @endif
            @if($ship->paragraph_family_kids)
                <h2>Familie & kinderen</h2>
                {!! '<p>' . str_replace("\r\n", '</p><p>', e($ship->paragraph_family_kids)) . '</p>' !!}
            @endif
            @if($ship->paragraph_fitness_relaxation)
                <h2>Fitness & ontspanning</h2>
                {!! '<p>' . str_replace("\r\n", '</p><p>', e($ship->paragraph_fitness_relaxation)) . '</p>' !!}
            @endif
            @if($ship->paragraph_entertainment_leisure)
                <h2>Entertainment & vrije tijd</h2>
                {!! '<p>' . str_replace("\r\n", '</p><p>', e($ship->paragraph_entertainment_leisure)) . '</p>' !!}
            @endif
            @if($ship->paragraph_huts_suites)
                <h2>Hutten & suites</h2>
                {!! '<p>' . str_replace("\r\n", '</p><p>', e($ship->paragraph_huts_suites)) . '</p>' !!}
            @endif
            @if($ship->paragraph_restaurants_buffet)
                <h2>Restaurants & buffet</h2>
                {!! '<p>' . str_replace("\r\n", '</p><p>', e($ship->paragraph_restaurants_buffet)) . '</p>' !!}
            @endif
            @if($ship->paragraph_decks)
                <h2>Dekkenplan</h2>
                {!! '<p>' . str_replace("\r\n", '</p><p>', e($ship->paragraph_decks)) . '</p>' !!}
            @endif
            @if($ship->paragraph_bars_lounges)
                <h2>Bars & lounges</h2>
                {!! '<p>' . str_replace("\r\n", '</p><p>', e($ship->paragraph_bars_lounges)) . '</p>' !!}
            @endif
        </div>
    </div>
</div>
@endsection