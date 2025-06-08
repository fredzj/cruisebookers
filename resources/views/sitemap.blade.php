@extends('layouts.app')

@section('title', 'Sitemap')

@section('content')
<div class="container my-4">
    <!-- Page Title -->
    <h1 class="mb-4">Sitemap</h1>

    <!-- Sitemap Links -->
    <ul>
        <!-- Home Page -->
        <li><a href="{{ url('/') }}">Home</a></li>

        <!-- Legal Pages -->
        <li><a href="{{ url('/cookieverklaring') }}">Cookieverklaring</a></li>
        <li><a href="{{ url('/disclaimer') }}">Disclaimer</a></li>
        <li><a href="{{ url('/privacyverklaring') }}">Privacyverklaring</a></li>

        <!-- Cruiselines and Ships -->
        <li>
          <a href="{{ route('cruiselines') }}">Cruisemaatschappijen</a>
          <ul>
            @foreach($cruiselines as $cru)
              <li>
                <a href="{{ route('cruiselines.show', $cru->slug) }}">{{ $cru->name }}</a>      
                @if($cru->cruiseships->isNotEmpty())
                  <ul>
                    @foreach($cru->cruiseships as $ship)
                      <li>
                        <a href="{{ route('cruiseships.show', [
                          'cruiseline' => $cru->slug,
                          'ship'       => $ship->slug
                        ]) }}">
                          {{ $ship->name }}
                        </a>
                      </li>
                    @endforeach
                  </ul>
                @endif      
              </li>
            @endforeach
          </ul>
        </li>
  
        <!-- Merchants Overview -->
        <li><a href="{{ route('partners') }}">Reisorganisaties</a>

        <!-- Merchant Detail Pages -->
            <ul>
                @foreach($merchants as $merchant)
                    <li><a href="{{ route('partners.show', $merchant->slug) }}">{{ $merchant->name }}</a></li>
                @endforeach
            </ul>
        </li>

        <!-- Travel Advice Overview -->
        <li><a href="{{ route('traveladvices') }}">Reisadviezen</a>
            <ul>
                @foreach($traveladvices as $advice)
                    <li><a href="{{ route('traveladvices.show', $advice->id) }}">{{ $advice->location }}</a></li>
                @endforeach
            </ul>
        </li>
    
        <!-- Blog Overview -->
        <li><a href="{{ route('blog.index') }}">Blog</a>

        <!-- Blog Detail Pages -->
            <ul>
                @foreach($blogs as $blog)
                    <li><a href="{{ route('blog.show', $blog->slug) }}">{{ $blog->title }}</a></li>
                @endforeach
            </ul>
        </li>
    </ul>
</div>
@endsection