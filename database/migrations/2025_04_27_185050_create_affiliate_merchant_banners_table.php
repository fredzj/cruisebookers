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
        Schema::create('affiliate_networks_merchants_ads', function (Blueprint $table) {
            $table->tinyIncrements('id'); // UNSIGNED TINYINT AUTO_INCREMENT PRIMARY KEY
            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP')); // TIMESTAMP with default current timestamp
            $table->mediumInteger('merchant_id')->unsigned()->nullable(); // UNSIGNED MEDIUMINT, nullable
            $table->string('merchant_slug', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_520_ci')->nullable(); // VARCHAR(255) with utf8mb4 collation
            $table->string('size', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_520_ci')->nullable(); // VARCHAR(255) with utf8mb4 collation
            $table->string('banner', 512)->charset('utf8mb4')->collation('utf8mb4_unicode_520_ci')->nullable(); // VARCHAR(512) with utf8mb4 collation
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_networks_merchants_ads');
    }
};