@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', $product->additional_data->accommodation_descriptionshort ?? 'Bekijk cruises van ' . $merchant->name)
@section('meta_image', $product->additional_data->images[0] ?? 'https://cruisebookers.nl/images/hero.jpg')
@section('meta_image_alt', $product->name)

@section('content')
<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/search') }}">Zoekresultaten</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            @if(isset($product->additional_data->images) && is_array($product->additional_data->images))
            <div class="gallery mt-4">
                <div class="main-image text-center mb-3">
                    <img id="mainImage" src="{{ $product->additional_data->images[0] }}" class="img-fluid rounded" alt="Main Image">
                </div>
                <div class="thumbnails d-flex flex-wrap justify-content-center">
                    @foreach($product->additional_data->images as $image)
                        <div class="thumbnail mx-2 mb-2">
                            <img src="{{ $image }}" class="img-thumbnail" alt="Thumbnail Image" style="cursor: pointer; max-width: 100px;" onclick="document.getElementById('mainImage').src='{{ $image }}'">
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        <div class="col-md-6">
            <h1>{{ $product->name }}</h1>
            @if(isset($product->additional_data->departure_country))
                <p><strong>Vertrek:</strong> {{ $product->additional_data->departure_country }}</p>
            @endif
            <p><strong>Bestemming:</strong>
                @if(isset($product->destination_country_name))
                    {{ $product->destination_country_name }}
                @endif
                @if(isset($product->destination_region_name))
                    / {{ $product->destination_region_name }}
                @endif
                @if(isset($product->destination_province_name))
                    / {{ $product->destination_province_name }}
                @endif
                @if(isset($product->destination_city_name))
                    / {{ $product->destination_city_name }}
                @endif
            </p>
            @if(isset($product->offer_departure_date))
                <p><strong>Vertrekdatum:</strong> {{ \Carbon\Carbon::parse($product->offer_departure_date)->translatedFormat('l j F Y') }}</p>
            @endif
            @if(isset($product->offer_duration_days))
                <p><strong>Duur:</strong> {{ $product->offer_duration_days }} dagen</p>
            @endif
            @if(isset($product->cruiseline_name))
                <p><strong>Cruisemaatschappij:</strong> <a href="/cruisemaatschappijen/{{ \Illuminate\Support\Str::slug($product->cruiseline_name) }}">{{ $product->cruiseline_name }}</a></p>
            @endif
            @if(isset($product->cruiseship_name))
                <p><strong>Cruiseschip:</strong> {{ $product->cruiseship_name }}</p>
            @endif
            @if(isset($product->price) && $product->price > 0)
               @php
                    $formatter = new \NumberFormatter('nl_NL', \NumberFormatter::CURRENCY);
                    $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0); // Remove decimals
                @endphp
                <p><strong>Prijs:</strong> v.a. {{ $formatter->formatCurrency($product->price, 'EUR') }} p.p.</p>
            @endif
            @if(isset($product->additional_data->offer_includedinprice))
                <p><strong>Inbegrepen in de prijs:</strong></p>
                @if(str_contains($product->additional_data->offer_includedinprice, '|'))
                    <ul>
                        @foreach(explode('|', $product->additional_data->offer_includedinprice) as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @else
                    <p>{{ $product->additional_data->offer_includedinprice }}</p>
                @endif
            @endif
            @if(isset($product->additional_data->offer_excludedfromprice))
                <p><strong>Niet inbegrepen in de prijs:</strong> {{ $product->additional_data->offer_excludedfromprice }}</p>
            @endif
            @if(isset($product->additional_data->accommodation_extrainfo))
                <p><strong>Extra Informatie:</strong> {{ $product->additional_data->accommodation_extrainfo }}</p>
            @endif
            @if(isset($product->additional_data->accommodation_descriptionshort))
                <p><strong>Korte Beschrijving:</strong> {{ $product->additional_data->accommodation_descriptionshort }}</p>
            @endif
            @if(isset($product->additional_data->description) && $product->additional_data->description <> '')
                <p><strong>Beschrijving:</strong> {!! $product->additional_data->description !!}</p>
            @endif
            @if(isset($product->additional_data->accommodation_descriptionlong))
                <p><strong>Uitgebreide Beschrijving:</strong> {!! $product->additional_data->accommodation_descriptionlong !!}</p>
            @endif
            @if(isset($product->additional_data->accommodation_facilities) && $product->additional_data->accommodation_facilities <> '')
                <p><strong>Faciliteiten:</strong> {{ $product->additional_data->accommodation_facilities }}</p>
            @endif
            @if(isset($product->additional_data->accommodation_usps))
                <p><strong>USP's:</strong> {{ $product->additional_data->accommodation_usps }}</p>
            @endif
            <a href="{{ $product->additional_data->url }}" class="btn btn-primary mt-3" target="_blank">Bekijk bij {{ $product->merchant_name }}</a>
        </div>
    </div>
</div>

<!-- Add Structured Data -->
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Product",
        "name": "{{ $product->name }}",
        "image": [
            @if(isset($product->additional_data->images) && is_array($product->additional_data->images))
                @foreach($product->additional_data->images as $image)
                    "{{ $image }}"{{ !$loop->last ? ',' : '' }}
                @endforeach
            @endif
        ],
        "description": "{{ $product->additional_data->description ?? '' }}",
        "brand": {
            "@type": "Brand",
            "name": "{{ $product->cruiseline_name ?? 'Unknown' }}"
        },
        "offers": {
            "@type": "Offer",
            "url": "{{ url()->current() }}",
            "priceCurrency": "EUR",
            "price": "{{ $product->price ?? 0 }}",
            "availability": "https://schema.org/InStock",
            "validFrom": "{{ \Carbon\Carbon::now()->toIso8601String() }}",
            "priceValidUntil": "{{ \Carbon\Carbon::now()->addYear()->toIso8601String() }}",
            "hasMerchantReturnPolicy": {
                "@type": "MerchantReturnPolicy",
                "name": "",
                "url": "",
                "returnPolicyCategory": "https://schema.org/MerchantReturnNotPermitted",
                "applicableCountry": "NL"
            },
            "shippingDetails": {
                "@type": "OfferShippingDetails",
                "shippingDestination": {
                    "@type": "DefinedRegion",
                    "addressCountry": "NL"
                },
                "deliveryTime": {
                    "@type": "ShippingDeliveryTime",
                    "handlingTime": {
                        "@type": "QuantitativeValue",
                        "minValue": 0,
                        "maxValue": 0,
                        "unitCode": "d" // Days
                    },
                    "transitTime": {
                        "@type": "QuantitativeValue",
                        "minValue": 0,
                        "maxValue": 0,
                        "unitCode": "d" // Days
                    }
                },
                "shippingRate": {
                    "@type": "MonetaryAmount",
                    "value": 0,
                    "currency": "EUR"
                }
            }
        }
        @if(!empty($product->additional_data->accommodation_rating))
        ,
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "{{ $product->additional_data->accommodation_rating ?? '' }}",
            "bestRating": "10",
            "reviewCount": ""
        }
        @endif
        ,
        "review": {
            "@type": "Review",
            "author": "",
            "datePublished": "",
            "description": ""
            @if(!empty($product->additional_data->accommodation_rating))
            ,
            "reviewRating": {
                "@type": "Rating",
                "ratingValue": "{{ $product->additional_data->accommodation_rating ?? '' }}"
            }
            @endif
        }
    }
</script>
@endsection