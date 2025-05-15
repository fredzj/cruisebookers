@extends('layouts.app')

@section('title', $cruiseline->meta_title . ' - CruiseBookers')
@section('meta_description', $cruiseline->meta_description ?? 'Bekijk cruises van ' . $cruiseline->name)

@section('content')
<div class="container-fluid p-0">
    @if(file_exists(public_path('images/cruiselines/youtube-' . $cruiseline->slug . '.jpg')))
        <div class="hero-image" style="background-image: url('{{ asset('images/cruiselines/youtube-' . $cruiseline->slug . '.jpg') }}'); height: 300px; background-size: cover; background-position: center;">
        </div>
    @endif
</div>

<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cruiselines') }}">Cruisemaatschappijen</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $cruiseline->name }}</li>
        </ol>
    </nav>

    <!-- Cruiseline Details -->
    <div class="row">
        <div class="col-md-4">
            @if(isset($cruiseline->url_logo))
                <img src="{{ $cruiseline->url_logo }}" class="img-fluid rounded mb-3" alt="{{ $cruiseline->name }}">
            @endif
            @if(isset($cruiseline->lists))
                {!! $cruiseline->lists !!}
            @endif
            <!-- CTA Button -->
            <a href="{{ route('search', ['cruiseline' => [$cruiseline->name]]) }}" class="btn btn-primary mt-4">
                Bekijk cruises van {{ $cruiseline->name }}
            </a>
        </div>
        <div class="col-md-8">
            <h1>{{ $cruiseline->name }}</h1>
            <h2>{{ $cruiseline->subtitle }}</h2>

            <!-- CTA Button -->
            <a href="{{ route('search', ['cruiseline' => [$cruiseline->name]]) }}" class="btn btn-primary mt-4 mb-4">
                Bekijk de aanbiedingen van {{ $cruiseline->name }}
            </a>

            @if(isset($cruiseline->lead_paragraph) && $cruiseline->lead_paragraph != '')
                <p class="lead">{!! $cruiseline->lead_paragraph !!}</p>
            @endif
            @if(isset($cruiseline->second_paragraph) && $cruiseline->second_paragraph != '')
                {!! $cruiseline->second_paragraph !!}
            @endif
            @if(isset($cruiseline->third_paragraph) && $cruiseline->third_paragraph != '')
                {!! $cruiseline->third_paragraph !!}
            @endif
            @if(isset($cruiseline->fourth_paragraph) && $cruiseline->fourth_paragraph != '')
                {!! $cruiseline->fourth_paragraph !!}
            @endif

            @if(isset($cruiseline->introduction) && $cruiseline->introduction != '')
                {!! $cruiseline->introduction !!}
            @endif
            @if(isset($cruiseline->description) && $cruiseline->description != '')
                {!! $cruiseline->description !!}
            @endif

            <!-- CTA Button -->
            <a href="{{ route('search', ['cruiseline' => [$cruiseline->name]]) }}" class="btn btn-primary mt-4">
                Bekijk de cruises van {{ $cruiseline->name }}
            </a>

            <div class="row mt-5">
                <h3 class="mb-4">Onze Schepen</h3>
                @if(isset($cruiseline->fleet_paragraph) && $cruiseline->fleet_paragraph != '')
                    {!! $cruiseline->fleet_paragraph !!}
                @endif
                @if(isset($cruiseline->cruiseships) && $cruiseline->cruiseships->count() > 0)
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                        @foreach($cruiseline->cruiseships->sortBy('name') as $ship)
                            <div class="col">
                                <div class="card h-100">
                                    <img src="{{ file_exists(public_path('images/cruiseships/' . $cruiseline->slug . '-' . $ship->slug . '.jpg')) 
                                        ? asset('images/cruiseships/' . $cruiseline->slug . '-' . $ship->slug . '.jpg') 
                                        : asset('images/cruiseships/placeholder.jpg') }}" 
                                        class="card-img-top" alt="{{ $ship->name }}">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">{{ $ship->name }}</h5>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p>Er zijn geen schepen beschikbaar voor deze cruisemaatschappij.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection