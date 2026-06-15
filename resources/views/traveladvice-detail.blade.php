@extends('layouts.app')

@section('title', 'Reisadvies ' . $country['location'] . ' - Veiligheid & Tips | Cruisebookers' )
@section('meta_description', 'Ontdek het actuele reisadvies voor ' . $country['location'] . ' . Lees alles over veiligheid, gezondheid, lokale regels en praktische tips voor je cruise of vakantie.' )

@section('content')
@php
    use App\Services\TravelAdviceContent;
@endphp
<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('traveladvices') }}">Reisadviezen</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $country['location'] }}</li>
        </ol>
    </nav>

    <h1 class="mb-4">Reisadvies {{ $country['location'] }}</h1>

    @if(empty($advices))
        <div class="alert alert-info" role="alert">Voor dit land is momenteel geen reisadvies beschikbaar.</div>
    @else
        @foreach($advices as $index => $advice)
            @php
                $map = TravelAdviceContent::legendMap($advice['files'] ?? null);
                $content = TravelAdviceContent::accordion($advice['content'] ?? null, 'advice-' . $index);
                $modified = TravelAdviceContent::formatDate($advice['last_modified_at'] ?? null);
            @endphp
            <div class="row mb-5">
                <div class="col-md-4">
                    @if($map !== null)
                        <img src="{{ $map['url'] }}" class="img-fluid rounded" alt="{{ $map['title'] }}">
                    @endif
                </div>
                <div class="col-md-8">
                    <h2 class="h4">
                        {{ $advice['title'] ?? 'Reisadvies' }}
                    </h2>
                    @if($modified !== '')
                        <p class="text-muted">Laatst gewijzigd: {{ $modified }}</p>
                    @endif
                    @if(!empty($advice['introduction']))
                        <div class="lead mb-3">{!! TravelAdviceContent::safeHtml($advice['introduction']) !!}</div>
                    @endif
                    @if($content !== '')
                        {!! $content !!}
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection