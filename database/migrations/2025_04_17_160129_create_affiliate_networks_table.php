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
        Schema::create('affiliate_networks', function (Blueprint $table) {
            $table->unsignedTinyInteger('affiliate_id')->default(0);
            $table->string('affiliate_name', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->unsignedTinyInteger('id')->nullable()->unique();
            $table->char('code', 2)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();
            $table->string('label', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();
            $table->string('url_merchant_logo', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();

            $table->primary('affiliate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_networks');
    }
};