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
        Schema::create('vendor_rijksoverheid_traveladvices', function (Blueprint $table) {
            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('id', 255)->default('');
            $table->string('type', 255);
            $table->string('canonical', 255)->default('');
            $table->string('dataurl', 255)->default('');
            $table->string('title', 255)->default('');
            $table->text('introduction');
            $table->string('location', 255)->default('');
            $table->string('modificationdate', 255);
            $table->text('modifications');
            $table->string('authorities', 255);
            $table->string('creators', 255);
            $table->timestamp('lastmodified')->default('0000-00-00 00:00:00');
            $table->string('issued', 255);
            $table->string('available', 255);
            $table->string('license', 255);
            $table->string('rightsholders', 255);
            $table->string('language', 255);

            $table->index('id', 'id_index');
            $table->index('location', 'location_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_rijksoverheid_traveladvices');
    }
};