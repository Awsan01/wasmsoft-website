<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Client quotes for the home page. A quote is only shown once the
     * person agreed to be quoted by name (has_consent).
     */
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->json('quote');
            $table->string('author_name', 120);
            $table->json('author_role')->nullable();
            $table->string('company', 150)->nullable();
            $table->foreignId('photo_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('has_consent')->default(false);
            $table->boolean('is_visible')->default(false);
            $table->unsignedInteger('position')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
