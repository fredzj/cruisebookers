@extends('layouts.app')

@section('title', 'Reisorganisaties')

@section('content')
<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reisorganisaties</li>
        </ol>
    </nav>

    <!-- Page Title -->
    <h1 class="mb-4">Reisorganisaties</h1>

    <!-- Merchant Grid -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-6 g-4">
        @foreach($merchants as $merchant)
            <div class="col">
                <a href="{{ route('partners.show', $merchant->slug) }}" class="text-decoration-none">
                    <div class="card h-100 text-center shadow rounded">
                        <img src="{{ $merchant->url_merchant_logo }}" class="card-img-top p-3" alt="{{ $merchant->name }}">
                        <div class="card-body bg-light">
                            <h5 class="card-title">{{ $merchant->name }}</h5>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection