@extends('layouts.app')

@section('title', 'Cruiselines')

@section('content')
<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cruisemaatschappijen</li>
        </ol>
    </nav>

    <!-- Page Title -->
    <h1 class="mb-4">Cruisemaatschappijen</h1>

    <!-- Cruiseline Grid -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-6 g-4">
        @foreach($cruiselines as $cruiseline)
            <div class="col">
                <a href="{{ route('cruiselines.show', $cruiseline->slug) }}" class="text-decoration-none">
                    <div class="card h-100 text-center shadow rounded">
                        <img src="{{ $cruiseline->url_logo }}" class="card-img-top p-3" alt="{{ $cruiseline->name }}">
                        <div class="card-body bg-light">
                            <h5 class="card-title">{{ $cruiseline->name }}</h5>
                            <p class="card-text">{{ $cruiseline->subtitle }}</p>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection