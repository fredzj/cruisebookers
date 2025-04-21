@extends('layouts.app')

@section('title', 'Zoekresultaten')

@section('content')
<div class="container my-4">
    <h1 class="mb-4">Zoekresultaten</h1>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-3">
            <h5>Filters</h5>
            <form method="GET" action="{{ route('search') }}" id="filter-form">

                <!-- Continent and Country Facets -->
                <div class="mb-3">
                    <h6>Bestemming</h6>
                    <div class="accordion" id="continentAccordion">
                        @foreach($facets['countriesByContinent'] as $continent => $countries)
                        <div class="mb-2">
                            <!-- Continent Checkbox -->
                            <div class="form-check">
                                <input class="form-check-input continent-checkbox" type="checkbox" name="continent[]" value="{{ $continent }}" id="continent-{{ $continent }}" {{ in_array($continent, request('continent', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="continent-{{ $continent }}">
                                    <strong>{{ $continent }}</strong>
                                </label>
                            </div>
            
                            <!-- Country Checkboxes -->
                            <div class="ms-3 countries-container" data-continent="{{ $continent }}" style="display: {{ in_array($continent, request('continent', [])) || collect($countries)->pluck('destination_country_name')->intersect(request('country', []))->isNotEmpty() ? 'block' : 'none' }};">
                                @foreach($countries as $country)
                                    <div class="form-check">
                                        <input class="form-check-input country-checkbox" type="checkbox" name="country[]" value="{{ $country->destination_country_name }}" id="country-{{ $country->destination_country_name }}" {{ in_array($country->destination_country_name, request('country', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="country-{{ $country->destination_country_name }}">
                                            {{ $country->destination_country_name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Cruiseline Facet -->
                <div class="mb-3">
                    <h6>Cruisemaatschappij</h6>
                    @foreach($facets['cruiseshipsByCruiseline'] as $cruiseline => $cruiseships)
                        <div class="mb-2">
                            <!-- Cruiseline Checkbox -->
                            <div class="form-check">
                                <input class="form-check-input cruiseline-checkbox" type="checkbox" name="cruiseline[]" value="{{ $cruiseline }}" id="cruiseline-{{ $cruiseline }}" {{ in_array($cruiseline, request('cruiseline', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="cruiseline-{{ $cruiseline }}">
                                    {{ $cruiseline }}
                                </label>
                            </div>
                        
                            <!-- Cruiseship Checkboxes -->
                            @if($cruiseships->isNotEmpty())
                                <div class="ms-3 cruiseships-container" data-cruiseline="{{ $cruiseline }}" style="display: {{ in_array($cruiseline, request('cruiseline', [])) || collect($cruiseships)->pluck('cruiseship_name')->intersect(request('cruiseship', []))->isNotEmpty() ? 'block' : 'none' }};">
                                    @foreach($cruiseships as $cruiseship)
                                        <div class="form-check">
                                            <input class="form-check-input cruiseship-checkbox" type="checkbox" name="cruiseship[]" value="{{ $cruiseship->cruiseship_name }}" id="cruiseship-{{ $cruiseship->cruiseship_name }}" {{ in_array($cruiseship->cruiseship_name, request('cruiseship', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="cruiseship-{{ $cruiseship->cruiseship_name }}">
                                                {{ $cruiseship->cruiseship_name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Merchant Facet -->
                <div class="mb-3">
                    <h6>Reisorganisatie</h6>
                    @foreach($facets['merchants'] as $merchant)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="merchant[]" value="{{ $merchant }}" id="merchant-{{ $merchant }}" {{ in_array($merchant, request('merchant', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="merchant-{{ $merchant }}">
                                {{ $merchant }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary d-none">Apply Filters</button>
            </form>
        </div>

        <!-- Search Results -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="mb-0">{{ $totalResults }} cruises gevonden</p>
                <form method="GET" action="{{ route('search') }}">
                    <select name="sort" class="form-select" onchange="this.form.submit()">
                        <option value="price_asc" {{ $sortBy == 'price_asc' ? 'selected' : '' }}>Prijs: laag - hoog</option>
                        <option value="price_desc" {{ $sortBy == 'price_desc' ? 'selected' : '' }}>Prijs: hoog - laag</option>
                        <option value="name_asc" {{ $sortBy == 'name_asc' ? 'selected' : '' }}>Naam: A - Z</option>
                        <option value="name_desc" {{ $sortBy == 'name_desc' ? 'selected' : '' }}>Naam: Z - A</option>
                    </select>
                </form>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                @foreach($products as $product)
                    <div class="col">
                        <div class="card h-100">
                            <img src="{{ $product->image }}" class="card-img-top search-result-image" alt="{{ $product->name }}">
                            <div class="card-body">
                                <h5 class="card-title">{{ $product->name }}</h5>
                                <p class="card-text">
                                    <br><i class="fa-solid fa-globe"></i> {{ $product->destination_country_name }}
                                    @if(isset($product->offer_departure_date))
                                        <br><i class="fa-solid fa-calendar"></i> {{ $product->offer_departure_date }}
                                    @endif
                                    @if(isset($product->offer_duration_days) && $product->offer_duration_days > 0)
                                        <br><i class="fa-solid fa-calendar-days"></i> {{ $product->offer_duration_days }} dagen
                                        @if(isset($product->offer_duration_nights) && $product->offer_duration_nights > 0)
                                            ({{ $product->offer_duration_nights }} nachten)
                                        @endif
                                    @endif
                                    @if(isset($product->offer_cruiseship) && isset($product->offer_cruiseline))
                                        <br><i class="fa-sharp fa-solid fa-ship"></i> {{ $product->offer_cruiseship }} {{ $product->offer_cruiseline }}</p>
                                    @endif
                                    @if(isset($product->holidaytype_is_rivercruise) && $product->holidaytype_is_rivercruise > 0)
                                        <br><i class="fa-sharp fa-solid fa-water"></i> riviercruise</p>
                                    @endif
                                    @if(isset($product->holidaytype_is_seacruise) && $product->holidaytype_is_seacruise > 0)
                                        <br><i class="fa-sharp fa-solid fa-water"></i> zeecruise</p>
                                    @endif
                                    @if(isset($product->accommodation_rating) && $product->accommodation_rating > 0)
                                        <br>Waardering: {{ $product->accommodation_rating }} / 10</p>
                                    @endif
                                    <br>
                                    @if(isset($product->holidaytype_is_all_inclusives) && $product->holidaytype_is_all_inclusives > 0)
                                        <i class="fa-sharp fa-solid fa-martini-glass-citrus"></i> all-inclusive</p>
                                    @endif
                                    @if(isset($product->holidaytype_is_lastminute) && $product->holidaytype_is_lastminute > 0)
                                        <i class="fa-sharp fa-solid fa-clock"></i> lastminute</p>
                                    @endif
                                    @if(isset($product->price) && $product->price > 0)
                                        <br>€{{ number_format($product->price, 2) }}</p>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $products->appends(request()->except('page'))->links('vendor.pagination.custom') }}
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const form = document.getElementById('filter-form');
                    const checkboxes = form.querySelectorAll('input[type="checkbox"]');
            
                    checkboxes.forEach(function (checkbox) {
                        checkbox.addEventListener('change', function () {
                            form.submit();
                        });
                    });

                    const continentCheckboxes = document.querySelectorAll('.continent-checkbox');
                    const countriesContainers = document.querySelectorAll('.countries-container');

                    // Toggle visibility of countries based on continent selection
                    continentCheckboxes.forEach(function (checkbox) {
                        checkbox.addEventListener('change', function () {
                            const continent = checkbox.value;
                            const container = document.querySelector(`.countries-container[data-continent="${continent}"]`);
                            if (checkbox.checked) {
                                container.style.display = 'block';
                            } else {
                                // Hide countries if no country is selected
                                const countryCheckboxes = container.querySelectorAll('.country-checkbox');
                                const anyCountrySelected = Array.from(countryCheckboxes).some(cb => cb.checked);
                                if (!anyCountrySelected) {
                                    container.style.display = 'none';
                                }
                            }
                        });
                    });

                    // Ensure countries remain visible if any country is selected
                    countriesContainers.forEach(function (container) {
                        const countryCheckboxes = container.querySelectorAll('.country-checkbox');
                        countryCheckboxes.forEach(function (checkbox) {
                            checkbox.addEventListener('change', function () {
                                const continent = container.getAttribute('data-continent');
                                const continentCheckbox = document.querySelector(`#continent-${continent}`);
                                if (checkbox.checked) {
                                    container.style.display = 'block';
                                    continentCheckbox.checked = true;
                                }
                            });
                        });
                    });

                    const cruiselineCheckboxes = document.querySelectorAll('.cruiseline-checkbox');
                    const cruiseshipsContainers = document.querySelectorAll('.cruiseships-container');

                    // Toggle visibility of cruiseships based on cruiseline selection
                    cruiselineCheckboxes.forEach(function (checkbox) {
                        checkbox.addEventListener('change', function () {
                            const cruiseline = checkbox.value;
                            const container = document.querySelector(`.cruiseships-container[data-cruiseline="${cruiseline}"]`);
                            if (checkbox.checked) {
                                container.style.display = 'block';
                            } else {
                                // Hide cruiseships if no cruiseship is selected
                                const cruiseshipCheckboxes = container.querySelectorAll('.cruiseship-checkbox');
                                const anyCruiseshipSelected = Array.from(cruiseshipCheckboxes).some(cb => cb.checked);
                                if (!anyCruiseshipSelected) {
                                    container.style.display = 'none';
                                }
                            }
                        });
                    });

                    // Ensure cruiseships remain visible if any cruiseship is selected
                    cruiseshipsContainers.forEach(function (container) {
                        const cruiseshipCheckboxes = container.querySelectorAll('.cruiseship-checkbox');
                        cruiseshipCheckboxes.forEach(function (checkbox) {
                            checkbox.addEventListener('change', function () {
                                const cruiseline = container.getAttribute('data-cruiseline');
                                const cruiselineCheckbox = document.querySelector(`#cruiseline-${cruiseline}`);
                                if (checkbox.checked) {
                                    container.style.display = 'block';
                                    cruiselineCheckbox.checked = true;
                                }
                            });
                        });
                    });
               });
            </script>
        </div>
    </div>
</div>
@endsection