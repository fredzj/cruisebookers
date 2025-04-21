@extends('layouts.app')

@section('title', $cruiseline->name)

@section('content')
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
            <img src="{{ $cruiseline->url_logo }}" class="img-fluid rounded" alt="{{ $cruiseline->name }}">
        </div>
        <div class="col-md-8">
            <h1>{{ $cruiseline->name }}</h1>
            <p class="lead">{{ $cruiseline->lead_paragraph }}</p>
            {!! $cruiseline->introduction !!}
            {!! $cruiseline->description !!}
        </div>
    </div>
</div>
@endsection