<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('affiliate_networks_merchants_productfeeds', function (Blueprint $table) {
            $table->timestamp('timestamp')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->mediumIncrements('id');
            $table->unsignedMediumInteger('merchant_id');
            $table->unsignedMediumInteger('feed_id')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->string('is_blocked_reason', 255)->nullable();
            $table->boolean('is_blocked_for_extraction')->default(false);
            $table->boolean('is_blocked_for_transformation')->default(false);
            $table->boolean('is_parks')->nullable();
            $table->boolean('is_name_missing')->default(false);
            $table->boolean('is_address_redundant')->default(false);
            $table->boolean('is_category_redundant')->default(false);
            $table->boolean('is_city_redundant')->default(false);
            $table->boolean('is_description_redundant')->default(false);
            $table->boolean('is_title_redundant')->default(false);
            $table->char('language_code', 2)->nullable();
            $table->string('name', 255);
            $table->text('url');
            $table->text('url_fields')->nullable();
            $table->unsignedSmallInteger('url_max_records')->nullable();
            $table->text('expected_properties')->nullable();
            $table->text('expected_variations')->nullable();

            $table->primary('id');
            $table->index('is_blocked');
            $table->index('is_blocked_for_extraction');
            $table->index('is_blocked_for_transformation');
            $table->index('merchant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_networks_merchants_productfeeds');
    }
};