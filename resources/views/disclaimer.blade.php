@extends('layouts.app')

@section('title', 'Disclaimer')
@section('robots', 'noindex, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1')

@section('content')
<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Disclaimer</li>
        </ol>
    </nav>

    <!-- Page Title -->
    <h1 class="mb-4">Disclaimer</h1>

    <!-- Disclaimer Content -->
    <p>CruiseBookers verleent u hierbij toegang tot cruisebookers.nl ("de Website") en nodigt u uit de hier aangeboden diensten af te nemen.</p>
    <p>CruiseBookers behoudt zich daarbij het recht voor op elk moment de inhoud aan te passen of onderdelen te verwijderen zonder daarover aan u mededeling te hoeven doen.</p>
    <h2>Beperkte aansprakelijkheid</h2>
    <p>CruiseBookers spant zich in om de inhoud van de Website zo vaak mogelijk te actualiseren en/of aan te vullen. Ondanks deze zorg en aandacht is het mogelijk dat inhoud onvolledig en/of onjuist is.</p>
    <p>De op de Website aangeboden materialen worden aangeboden zonder enige vorm van garantie of aanspraak op juistheid. Deze materialen kunnen op elk moment wijzigen zonder voorafgaande mededeling van CruiseBookers.</p>
    <p>In het bijzonder zijn alle prijzen op de Website onder voorbehoud van type- en programmeerfouten. Voor de gevolgen van dergelijke fouten wordt geen aansprakelijkheid aanvaard. Geen overeenkomst komt tot stand op basis van dergelijke fouten. De inhoud van de Website wordt gedeeltelijk langs geautomatiseerde weg verkregen. CruiseBookers doet haar uiterste best deze inhoud zo vaak mogelijk te actualiseren en/of aan te vullen. Desondanks is het mogelijk dat deze inhoud onvolledig, achterhaald en/of onjuist is.</p>
    <h2>Auteursrechten</h2>
    <p>Alle rechten van intellectuele eigendom betreffende deze materialen liggen bij CruiseBookers en haar licentiegevers en bezoekers.</p>
    <p>Kopiëren, verspreiden en elk ander gebruik van deze materialen is niet toegestaan zonder schriftelijke toestemming van CruiseBookers, behoudens en slechts voor zover anders bepaald in regelingen van dwingend recht (zoals citaatrecht), tenzij bij specifieke materialen anders aangegeven is.</p>
    <h2>Affiliate</h2>
    <p>De site bevat links naar affiliate websites en wij ontvangen een affiliate commissie voor elke aankoop die u via dergelijke links doet op de affiliate website. Onze affiliates zijn onder andere:</p>
    <ul>
    <li>Awin
    <li>Daisycon
    <li>Tradedoubler
    <li>TradeTracker
    </ul>
    <h2>Overig</h2>
    <p>Deze disclaimer kan van tijd tot tijd wijzigen.</p>
</div>
@endsection