<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('position')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('post_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 100);
            $table->string('slug', 120);
            $table->timestamps();

            $table->unique(['post_category_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('tag_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 100);
            $table->string('slug', 120);
            $table->timestamps();

            $table->unique(['tag_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            // Accounts are normally deactivated, not deleted; if one is, its posts stay.
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('post_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cover_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['author_id', 'status']);
        });

        Schema::create('post_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title', 200);
            $table->string('slug', 120);
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 200)->nullable();
            $table->timestamps();

            $table->unique(['post_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('post_tag', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['post_id', 'tag_id']);
            $table->index('tag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('post_translations');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('tag_translations');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('post_category_translations');
        Schema::dropIfExists('post_categories');
    }
};
