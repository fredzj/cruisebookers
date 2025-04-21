@extends('layouts.app')

@section('title', $traveladvice->location)

@section('content')
<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('traveladvices') }}">Reisadviezen</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $traveladvice->location }}</li>
        </ol>
    </nav>

    <!-- Travel Advice Details -->
    <div class="row">
        <div class="col-md-4">
            <img src="{{ $traveladvice->fileurl }}" class="img-fluid rounded" alt="{{ $traveladvice->title }}">
            <p><small>Bron: {{ $traveladvice->authorities }}</small></p>
        </div>
        <div class="col-md-8">
            <h1>Reisadvies {{ $traveladvice->location }}</h1>
            <p>{{ $traveladvice->modificationdate }}</p>
            <p class="lead">{{ $traveladvice->modifications }}</p>
            <p>{!! str_replace('a href', 'a target="_blank" href', $traveladvice->introduction) !!}</p>
            <div class="accordion mb-4" id="accordionContentBlocks">
                <div class="accordion-item">
                    @foreach($contentblocks as $block)
                        <h3 class="accordion-header" id="heading{{ $block->sequence }}">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $block->sequence }}" aria-expanded="false" aria-controls="collapse{{ $block->sequence }}">{{ $block->paragraphtitle }}</button>
                        </h3>
                        <div id="collapse{{ $block->sequence }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $block->sequence }}" data-bs-parent="#accordionContentBlocks">
                            <div class="accordion-body">
                                {!! str_replace('<h4>', '<h4 class="h6">', $block->paragraph) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection