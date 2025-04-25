@extends('layouts.app')

@section('title', 'Zoekresultaten')

@section('content')
<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Zoekresultaten</li>
        </ol>
    </nav>

    <h1 class="mb-4">Zoekresultaten</h1>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-3">
            <h5>Filters</h5>
            <form method="GET" action="{{ route('search') }}" id="filter-form">

                <!-- Cruisetype Facet -->
                <div class="mb-3">
                    <h6>Cruisetype</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="cruiseline_category[]" value="zeecruise" id="seacruise" {{ in_array('zeecruise', request('cruiseline_category', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="seacruise">
                            Zeecruise
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="cruiseline_category[]" value="riviercruise" id="rivercruise" {{ in_array('riviercruise', request('cruiseline_category', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="rivercruise">
                            Riviercruise
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="cruiseline_category[]" value="minicruise" id="minicruise" {{ in_array('minicruise', request('cruiseline_category', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="minicruise">
                            Minicruise
                        </label>
                    </div>
                </div>

                <!-- Departure Year and Month Filter -->
                <div class="mb-3">
                    <h6>Vertrekdatum</h6>
                    <div class="accordion" id="departureAccordion">
                        @foreach($facets['monthsByYear'] as $year => $months)
                            <div class="mb-2">
                                <!-- Year Checkbox -->
                                <div class="form-check">
                                    <input class="form-check-input departure-year-checkbox" type="checkbox" name="departure_year[]" value="{{ $year }}" id="departure-year-{{ $year }}" {{ in_array($year, request('departure_year', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="departure-year-{{ $year }}">
                                        {{ $year }}
                                    </label>
                                </div>
                            
                                <!-- Month Checkboxes -->
                                <div class="ms-3 months-container" data-year="{{ $year }}" style="display: {{ in_array($year, request('departure_year', [])) ? 'block' : 'none' }};">
                                    @foreach($months as $month)
                                        <div class="form-check">
                                            <input class="form-check-input departure-month-checkbox" type="checkbox" name="departure_month[]" value="{{ $month->month }}" id="departure-month-{{ $year }}-{{ $month->month }}" {{ in_array($month->month, request('departure_month', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="departure-month-{{ $year }}-{{ $month->month }}">
                                                {{ DateTime::createFromFormat('!m', $month->month)->format('F') }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

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
                                    {{ $continent }}
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

            <!-- Selected Filters -->
            <div class="mb-3">
                <h6>Geselecteerde Filters:</h6>
                <div>
                    <!-- Cruisetype Badges -->
                    @if(request()->has('cruiseline_category'))
                        @foreach(request('cruiseline_category') as $category)
                            <span class="badge bg-primary me-2">
                                {{ ucfirst($category) }}
                                <a href="{{ route('search', array_merge(request()->except('cruiseline_category', 'page'), ['cruiseline_category' => array_diff(request('cruiseline_category'), [$category])])) }}" class="text-white ms-1">
                                    &times;
                                </a>
                            </span>
                        @endforeach
                    @endif

                    <!-- Departure Year Badge -->
                    @if(request()->has('departure_year'))
                        @foreach(request('departure_year') as $year)
                            <span class="badge bg-warning me-2">
                                Jaar: {{ $year }}
                                <a href="{{ route('search', request()->except(['departure_year', 'page'])) }}" class="text-white ms-1">
                                    &times;
                                </a>
                            </span>
                        @endforeach
                    @endif
                        
                    <!-- Departure Month Badge -->
                    @if(request()->has('departure_month'))
                        @foreach(request('departure_month') as $month)
                            <span class="badge bg-warning me-2">
                                Maand: {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                                <a href="{{ route('search', request()->except(['departure_month', 'page'])) }}" class="text-white ms-1">
                                    &times;
                                </a>
                            </span>
                        @endforeach
                    @endif

                    <!-- Continent Badges -->
                    @if(request()->has('continent'))
                        @foreach(request('continent') as $continent)
                            <span class="badge bg-primary me-2">
                                {{ $continent }}
                                <a href="{{ route('search', array_merge(request()->except('continent', 'page'), ['continent' => array_diff(request('continent'), [$continent])])) }}" class="text-white ms-1">
                                    &times;
                                </a>
                            </span>
                        @endforeach
                    @endif
                        
                    <!-- Country Badges -->
                    @if(request()->has('country'))
                        @foreach(request('country') as $country)
                            <span class="badge bg-secondary me-2">
                                {{ $country }}
                                <a href="{{ route('search', array_merge(request()->except('country', 'page'), ['country' => array_diff(request('country'), [$country])])) }}" class="text-white ms-1">
                                    &times;
                                </a>
                            </span>
                        @endforeach
                    @endif
                        
                    <!-- Cruiseline Badges -->
                    @if(request()->has('cruiseline'))
                        @foreach(request('cruiseline') as $cruiseline)
                            <span class="badge bg-success me-2">
                                {{ $cruiseline }}
                                <a href="{{ route('search', array_merge(request()->except('cruiseline', 'page'), ['cruiseline' => array_diff(request('cruiseline'), [$cruiseline])])) }}" class="text-white ms-1">
                                    &times;
                                </a>
                            </span>
                        @endforeach
                    @endif
                        
                    <!-- Cruiseship Badges -->
                    @if(request()->has('cruiseship'))
                        @foreach(request('cruiseship') as $cruiseship)
                            <span class="badge bg-info me-2">
                                {{ $cruiseship }}
                                <a href="{{ route('search', array_merge(request()->except('cruiseship', 'page'), ['cruiseship' => array_diff(request('cruiseship'), [$cruiseship])])) }}" class="text-white ms-1">
                                    &times;
                                </a>
                            </span>
                        @endforeach
                    @endif
                        
                    <!-- Clear All Filters -->
                    @if(request()->hasAny(['cruiseline_category', 'departure_year', 'departure_month', 'continent', 'country', 'cruiseline', 'cruiseship']))
                        <a href="{{ route('search', request()->except(['cruiseline_category', 'departure_year', 'departure_month', 'continent', 'country', 'cruiseline', 'cruiseship', 'page'])) }}" class="btn btn-link text-danger">
                            Verwijder alle filters
                        </a>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="mb-0">{{ $totalResults }} cruise aanbiedingen gevonden</p>
                <form method="GET" action="{{ route('search') }}">
                    @foreach(request()->except('sort', 'page') as $key => $values)
                        @if(is_array($values))
                            @foreach($values as $value)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $value }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $values }}">
                        @endif
                    @endforeach
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
                        <a href="{{ route('product.show', ['slug' => $product->slug]) }}" class="text-decoration-none">
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
                                        @if(isset($product->cruiseline_name))
                                            <br><i class="fa-sharp fa-solid fa-building"></i> {{ $product->cruiseline_name }}
                                        @endif
                                        @if(isset($product->cruiseship_name))
                                            <br><i class="fa-sharp fa-solid fa-ship"></i> {{ $product->cruiseship_name }}
                                        @endif
                                        @if(isset($product->cruiseline_category) && $product->cruiseline_category == 'minicruise')
                                            <br><i class="fa-sharp fa-solid fa-water"></i> minicruise
                                        @endif
                                        @if(isset($product->cruiseline_category) && $product->cruiseline_category == 'riviercruise')
                                            <br><i class="fa-sharp fa-solid fa-water"></i> riviercruise
                                        @endif
                                        @if(isset($product->cruiseline_category) && $product->cruiseline_category == 'zeecruise')
                                            <br><i class="fa-sharp fa-solid fa-water"></i> zeecruise
                                        @endif
                                        @if(isset($product->holidaytype_is_all_inclusives) && $product->holidaytype_is_all_inclusives > 0)
                                            <i class="fa-sharp fa-solid fa-martini-glass-citrus"></i> all-inclusive
                                        @endif
                                        @if(isset($product->holidaytype_is_lastminutes) && $product->holidaytype_is_lastminutes > 0)
                                            <i class="fa-sharp fa-solid fa-clock"></i> lastminute
                                        @endif
                                        @if(isset($product->price) && $product->price > 0)
                                            @php
                                                $formatter = new \NumberFormatter('nl_NL', \NumberFormatter::CURRENCY);
                                                $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0); // Remove decimals
                                            @endphp
                                            <br>
                                            <span class="badge bg-success">
                                                <i class="fa-solid fa-tag"></i> v.a. {{ $formatter->formatCurrency($product->price, 'EUR') }} p.p.
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </a>
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

                    const yearCheckboxes = document.querySelectorAll('.departure-year-checkbox');
                    const monthsContainers = document.querySelectorAll('.months-container');

                    // Toggle visibility of months based on year selection
                    yearCheckboxes.forEach(function (checkbox) {
                        checkbox.addEventListener('change', function () {
                            const year = checkbox.value;
                            const container = document.querySelector(`.months-container[data-year="${year}"]`);
                            if (checkbox.checked) {
                                container.style.display = 'block';
                                // Check all months if the year is checked
                                container.querySelectorAll('.departure-month-checkbox').forEach(function (monthCheckbox) {
                                    monthCheckbox.checked = true;
                                });
                            } else {
                                // Uncheck all months if the year is unchecked
                                container.querySelectorAll('.departure-month-checkbox').forEach(function (monthCheckbox) {
                                    monthCheckbox.checked = false;
                                });
                                container.style.display = 'none';
                            }
                        });
                    });

                    // Ensure year checkbox is checked if any month is selected
                    monthsContainers.forEach(function (container) {
                        const year = container.getAttribute('data-year');
                        const yearCheckbox = document.querySelector(`#departure-year-${year}`);
                        const monthCheckboxes = container.querySelectorAll('.departure-month-checkbox');
                    
                        monthCheckboxes.forEach(function (checkbox) {
                            checkbox.addEventListener('change', function () {
                                const allChecked = Array.from(monthCheckboxes).every(cb => cb.checked);
                                const anyChecked = Array.from(monthCheckboxes).some(cb => cb.checked);
                            
                                if (anyChecked) {
                                    yearCheckbox.checked = true;
                                    container.style.display = 'block';
                                } else if (!anyChecked) {
                                    yearCheckbox.checked = false;
                                }
                            });
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