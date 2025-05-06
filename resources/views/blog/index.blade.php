{{-- filepath: d:\Work\Clients\FredOnis\cruisebookers\resources\views\blog\index.blade.php --}}
@extends('layouts.app')

@section('title', 'Blog - CruiseBookers')
@section('meta_description', 'Read the latest blog posts on CruiseBookers.')

@section('content')
<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Blogs</li>
        </ol>
    </nav>

    <h1>Blog</h1>
    <div class="row">
        @foreach($blogs as $blog)
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $blog->title }}</h5>
                        <p class="card-text">{!! Str::limit($blog->body, 100) !!}</p>
                        <a href="{{ route('blog.show', $blog->slug) }}" class="btn btn-primary">Lees verder</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    {{ $blogs->links() }}
</div>
@endsection