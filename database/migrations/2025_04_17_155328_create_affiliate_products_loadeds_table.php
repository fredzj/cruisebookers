<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_products_loaded', function (Blueprint $table) {
            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->mediumIncrements('id');
            $table->unsignedMediumInteger('merchant_id');
            $table->unsignedMediumInteger('productfeed_id');
            $table->string('product_id', 255);
            $table->string('campaign_id', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->decimal('price', 9, 2)->unsigned()->nullable();
            $table->string('url', 512);
            $table->text('images')->nullable();
            $table->text('description')->nullable();
            $table->string('categorypath', 255)->nullable();
            $table->string('categories', 255)->nullable();
            $table->string('subcategories', 255)->nullable();
            $table->string('subsubcategories', 255)->nullable();
            $table->text('accommodation_descriptionlong')->nullable();
            $table->text('accommodation_descriptionshort')->nullable();
            $table->text('accommodation_extrainfo')->nullable();
            $table->longText('accommodation_facilities')->nullable();
            $table->boolean('accommodation_is_childrenallowed')->nullable();
            $table->decimal('accommodation_rating', 4, 2)->unsigned()->nullable();
            $table->decimal('accommodation_stars', 2, 1)->unsigned()->nullable();
            $table->string('accommodation_type', 255)->nullable();
            $table->text('accommodation_usps')->nullable();
            $table->string('departure_continent', 255)->nullable();
            $table->string('departure_country', 255)->nullable();
            $table->string('departure_city', 255)->nullable();
            $table->string('destination_continent_name', 255)->nullable();
            $table->char('destination_country_code', 2)->nullable();
            $table->string('destination_country_name', 255)->nullable();
            $table->char('destination_region_code', 6)->nullable();
            $table->string('destination_region_name', 255)->nullable();
            $table->string('destination_province_name', 255)->nullable();
            $table->string('destination_city_name', 255)->nullable();
            $table->decimal('destination_latitude', 10, 8)->nullable();
            $table->decimal('destination_longitude', 10, 8)->nullable();
            $table->boolean('holidaytype_is_all_inclusives')->default(false);
            $table->boolean('holidaytype_is_lastminutes')->default(false);
            $table->boolean('holidaytype_is_minicruise')->default(false);
            $table->boolean('holidaytype_is_rivercruise')->default(false);
            $table->boolean('holidaytype_is_rivercruise_danube')->default(false);
            $table->boolean('holidaytype_is_rivercruise_moselle')->default(false);
            $table->boolean('holidaytype_is_rivercruise_nile')->default(false);
            $table->boolean('holidaytype_is_rivercruise_rhine')->default(false);
            $table->boolean('holidaytype_is_rivercruise_volga')->default(false);
            $table->boolean('holidaytype_is_seacruise')->default(false);
            $table->boolean('holidaytype_is_seacruise_antarctic')->default(false);
            $table->boolean('holidaytype_is_seacruise_arctic')->default(false);
            $table->boolean('holidaytype_is_seacruise_bluecruise')->default(false);
            $table->boolean('holidaytype_is_seacruise_caribbean')->default(false);
            $table->boolean('holidaytype_is_seacruise_hurtigruten')->default(false);
            $table->boolean('holidaytype_is_seacruise_mediterranean')->default(false);
            $table->boolean('holidaytype_is_seacruise_sailing')->default(false);
            $table->boolean('holidaytype_is_seacruise_world')->default(false);
            $table->boolean('holidaytype_is_herfstvakantie')->default(false);
            $table->boolean('holidaytype_is_kerstvakantie')->default(false);
            $table->boolean('holidaytype_is_meivakantie')->default(false);
            $table->boolean('holidaytype_is_voorjaarsvakantie')->default(false);
            $table->boolean('holidaytype_is_zomervakantie')->default(false);
            $table->string('offer_cruiseline', 255)->nullable();
            $table->string('offer_cruiseship', 255)->nullable();
            $table->string('offer_departure_airport', 255)->nullable();
            $table->date('offer_departure_date')->nullable();
            $table->unsignedTinyInteger('offer_duration')->nullable();
            $table->unsignedTinyInteger('offer_duration_days')->nullable();
            $table->unsignedTinyInteger('offer_duration_nights')->nullable();
            $table->string('offer_duration_type', 255)->nullable();
            $table->string('offer_excludedfromprice', 255)->nullable();
            $table->boolean('offer_flight_included')->default(false);
            $table->string('offer_iata_arrival', 255)->nullable();
            $table->string('offer_iata_departure', 255)->nullable();
            $table->string('offer_includedinprice', 255)->nullable();
            $table->char('offer_isocode_departure', 2)->nullable();
            $table->decimal('offer_price', 9, 2)->unsigned()->nullable();
            $table->string('offer_price_type', 255)->nullable();
            $table->string('offer_service_type', 255)->nullable();
            $table->unsignedSmallInteger('offer_schoolholiday_id')->nullable();
            $table->string('offer_transport_type', 255)->nullable();

            $table->primary('id');
            $table->index('offer_schoolholiday_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_products_loaded');
    }
};
