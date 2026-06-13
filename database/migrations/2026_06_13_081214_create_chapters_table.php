<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('manga_id')->constrained('mangas')->cascadeOnDelete();
            $table->decimal('chapter_number', 8, 2);
            $table->string('chapter_title')->nullable();
            $table->string('source_chapter_id')->index();
            $table->json('pages_data');
            $table->string('base_url_override')->nullable();
            $table->string('path_override')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
