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
        Schema::create('affiliate_cruiseships', function (Blueprint $table) {
            $table->smallIncrements('cruiseship_id'); // Primary key with auto-increment
            $table->unsignedTinyInteger('cruiseline_id')->default(0)->index();
            $table->string('class', 255);
            $table->text('description');
            $table->string('facilities', 255);
            $table->string('introduction', 255);
            $table->unsignedTinyInteger('is_blocked')->default(0)->index();
            $table->unsignedSmallInteger('length')->default(0);
            $table->string('name', 255);
            $table->string('name_alt', 255);
            $table->unsignedSmallInteger('number_of_crew')->default(0);
            $table->unsignedTinyInteger('number_of_decks')->default(0);
            $table->unsignedSmallInteger('number_of_passengers')->default(0)->comment('double occupancy');
            $table->unsignedSmallInteger('number_of_passengers_max')->default(0)->comment('maximum capacity');
            $table->unsignedSmallInteger('number_of_rooms')->default(0);
            $table->string('slug', 255);
            $table->unsignedDecimal('stars', 2, 1)->default(0.0);
            $table->string('type', 255);
            $table->unsignedSmallInteger('year_built')->default(0);
            $table->unsignedSmallInteger('year_first_in_service')->default(0);
            $table->unsignedSmallInteger('year_last_refurbishment')->default(0);
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_cruiseships');
    }
};