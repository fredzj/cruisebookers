@extends('layouts.app')

@section('title', 'Reisadviezen')

@section('content')
<div class="container my-4">
    <!-- Page Title -->
    <h1 class="mb-4">Reisadviezen</h1>

    <!-- Travel Advice Grid -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-6 g-4">
        @foreach($traveladvices as $advice)
            <div class="col">
                <a href="{{ route('traveladvices.show', $advice->id) }}" class="text-decoration-none">
                    <div class="card h-100 text-center shadow rounded">
                        <img src="{{ $advice->fileurl }}" class="card-img-top p-3" alt="{{ $advice->location }}">
                        <div class="card-body bg-light">
                            <h5 class="card-title">{{ $advice->location }}</h5>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection