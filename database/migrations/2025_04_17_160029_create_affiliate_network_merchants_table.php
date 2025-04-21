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
        Schema::create('affiliate_networks_merchants', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->char('affiliate_network_code', 2);
            $table->date('date_blocked')->nullable()->comment('CRUD');
            $table->text('description')->nullable()->comment('CRUD');
            $table->string('domain_name', 255);
            $table->unsignedTinyInteger('is_blocked')->comment('CRUD');
            $table->boolean('is_bookingexperts')->nullable()->comment('CRUD');
            $table->boolean('is_flighttickets')->nullable()->comment('CRUD');
            $table->string('name', 255);
            $table->unsignedMediumInteger('program_id')->nullable()->unique();
            $table->string('slug', 255)->unique()->comment('CRUD');
            $table->enum('type', [
                'camping',
                'hotelketen',
                'reisbureau',
                'touroperator',
                'vakantiepark',
                'vakantiewoningverhuur'
            ])->nullable()->comment('CRUD');
            $table->string('url_deeplink', 255)->nullable();

            $table->primary('id');
            $table->index('is_blocked');
            $table->index('affiliate_network_code');
            $table->index('is_flighttickets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_networks_merchants');
    }
};