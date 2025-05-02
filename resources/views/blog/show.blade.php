{{-- filepath: d:\Work\Clients\FredOnis\cruisebookers\resources\views\blog\show.blade.php --}}
@extends('layouts.app')

@section('title', $blog->meta_title ?? $blog->title)
@section('meta_description', $blog->meta_description ?? Str::limit($blog->body, 150))

@section('content')
<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="/blog">Blogs</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $blog->title }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-4">
        </div>
        <div class="col-md-8">

            <h1>{{ $blog->title }}</h1>
            <p class="text-muted">{{ $blog->timestamp->format('F d, Y') }}</p>
            <div class="content">
                {!! $blog->body !!}
            </div>
        </div>
    </div>
</div>
@endsection