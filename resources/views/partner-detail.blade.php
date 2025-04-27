@extends('layouts.app')

@section('title', $merchant->name)

@section('content')
<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('partners') }}">Reisorganisaties</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $merchant->name }}</li>
        </ol>
    </nav>

    <!-- Partner Details -->
    <div class="row">
        <div class="col-md-4">
            <img src="{{ $merchant->url_merchant_logo }}" class="img-fluid rounded mb-3" alt="{{ $merchant->name }}">
            <!-- Trust Icons -->
            <div class="trust-icons mt-3 d-flex justify-content-start">
                @if(isset($merchant->is_member_anvr) && $merchant->is_member_anvr == 1)
                    <img src="{{ asset('images/logo-anvr.svg') }}" alt="ANVR Logo" class="me-2" style="width: 40px; height: auto;">
                @endif
                @if(isset($merchant->is_member_cf) && $merchant->is_member_cf == 1)
                    <img src="{{ asset('images/logo-calamiteitenfonds.svg') }}" alt="Calamiteitenfonds Logo" class="me-2" style="width: 40px; height: auto;">
                @endif
                @if(isset($merchant->is_member_sgr) && $merchant->is_member_sgr == 1)
                    <img src="{{ asset('images/logo-sgr.svg') }}" alt="SGR Logo" class="me-2" style="width: 40px; height: auto;">
                @endif
            </div>
        </div>
        <div class="col-md-8">
            <h1>{{ $merchant->name }}</h1>
            <p>{{ $merchant->type }}</p>
            <p>{{ $merchant->description }}</p>

            <!-- CTA Button -->
            @if($merchant->has_products)
                <a href="{{ route('search', ['merchant' => [$merchant->name]]) }}" class="btn btn-primary mt-4">
                    Bekijk cruises van {{ $merchant->name }}
                </a>
            @else
                <a href="{{ $merchant->url_deeplink }}" class="btn btn-secondary mt-4" target="_blank">
                    Bezoek de website van {{ $merchant->name }}
                </a>
            @endif
        </div>
    </div>
</div>
@endsection