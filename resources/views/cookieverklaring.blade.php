@extends('layouts.app')

@section('title', 'Cookieverklaring')
@section('robots', 'noindex, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1')

@section('content')
<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cookieverklaring</li>
        </ol>
    </nav>

    <!-- Page Title -->
    <h1 class="mb-4">Cookieverklaring</h1>

    <!-- Cookieverklaring Content -->
    <p>Deze website maakt gebruik van cookies. We gebruiken cookies om content en advertenties te personaliseren, om functies voor social media te bieden en om ons websiteverkeer te analyseren. Ook delen we informatie over uw gebruik van onze site met onze partners voor social media, adverteren en analyse. Deze partners kunnen deze gegevens combineren met andere informatie die u aan ze heeft verstrekt of die ze hebben verzameld op basis van uw gebruik van hun services.</p>
	<p>Cookies zijn kleine tekstbestanden die door websites kunnen worden gebruikt om gebruikerservaringen efficiënter te maken.</p>
	<p>Volgens de wet mogen wij cookies op uw apparaat opslaan als ze strikt noodzakelijk zijn voor het gebruik van de site. Voor alle andere soorten cookies hebben we uw toestemming nodig.</p>
	<p>Deze website maakt gebruik van verschillende soorten cookies. Sommige cookies worden geplaatst door diensten van derden die op onze pagina's worden weergegeven.</p>
	<p>Via de cookieverklaring op onze website kunt u uw toestemming op elk moment wijzigen of intrekken.</p>
	<p>In ons privacybeleid vindt u meer informatie over wie we zijn, hoe u contact met ons kunt opnemen en hoe we persoonlijke gegevens verwerken.</p>
	<p>Als u vragen heeft over uw toestemming, vermeld dan het ID en de datum van de toestemming alstublieft.</p>
	<p>Uw toestemming geldt voor de volgende gebieden: cruisebookers.nl</p>
</div>
@endsection