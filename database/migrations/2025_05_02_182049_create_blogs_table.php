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
        Schema::create('blog', function (Blueprint $table) {
            $table->tinyIncrements('id'); // UNSIGNED TINYINT AUTO_INCREMENT PRIMARY KEY
            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP')); // TIMESTAMP with default current timestamp
            $table->string('meta_title', 255)->nullable(); // VARCHAR(255), nullable
            $table->string('meta_description', 255)->nullable(); // VARCHAR(255), nullable
            $table->string('title', 255)->nullable(); // VARCHAR(255), nullable
            $table->text('body')->nullable(); // TEXT, nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog');
    }
};