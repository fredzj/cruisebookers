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
        Schema::create('vendor_rijksoverheid_traveladvice_contentblocks', function (Blueprint $table) {
            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('id', 255)->default('');
            $table->text('paragraph');
            $table->string('paragraphtitle', 255)->default('');
            $table->unsignedTinyInteger('sequence');

            $table->index('id', 'id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_rijksoverheid_traveladvice_contentblocks');
    }
};