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
        Schema::create('affiliate_cruiselines', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->default(0)->primary();
            $table->unsignedTinyInteger('cruiseline_type')->default(0)->comment('1=seacruise, 2=rivercruise');
            $table->text('description')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->text('introduction')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->unsignedTinyInteger('is_blocked')->default(0);
            $table->unsignedTinyInteger('is_member_anvr')->default(0);
            $table->unsignedTinyInteger('is_member_cf')->default(0);
            $table->unsignedTinyInteger('is_member_sgr')->default(0);
            $table->string('meta_title', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('meta_description', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('name', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('slogan', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('slug', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('url_logo', 255)->nullable();
            $table->string('url_media', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('url_presskit', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('url_pressreleases', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('url_videos', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');

            $table->index('slug', 'slug_index');
            $table->index('is_blocked', 'is_blocked_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_cruiselines');
    }
};