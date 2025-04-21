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
        Schema::create('vendor_rijksoverheid_traveladvice_files', function (Blueprint $table) {
            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('id', 255)->default('');
            $table->string('fileurl', 255)->nullable();
            $table->string('mimetype', 255)->nullable();
            $table->string('filesize', 255)->nullable();
            $table->string('filename', 255)->nullable();
            $table->string('filetitle', 255)->nullable();
            $table->string('filedescription', 255)->nullable();
            $table->string('filemodifieddate', 255)->nullable();
            $table->string('maptype', 255)->nullable();

            $table->index('id', 'id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_rijksoverheid_traveladvice_files');
    }
};