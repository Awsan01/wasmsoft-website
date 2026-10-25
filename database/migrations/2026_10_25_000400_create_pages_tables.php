<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            // Set for application-defined pages (home, listing intros); null for custom pages.
            $table->string('system_key', 30)->nullable()->unique();
            $table->string('template', 30)->default('default');
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        Schema::create('page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title', 200);
            // Null for system pages, whose URL is fixed.
            $table->string('slug', 120)->nullable();
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 200)->nullable();
            $table->timestamps();

            $table->unique(['page_id', 'locale']);
            // One URL per language; enforced by the database, not only by validation.
            $table->unique(['locale', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_translations');
        Schema::dropIfExists('pages');
    }
};
