<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Short statements shown on the home and about pages: differentiators
     * ("why work with us"), values and metrics. Texts are locale => text
     * maps; a metric's figure is the same in every language.
     */
    public function up(): void
    {
        Schema::create('highlights', function (Blueprint $table) {
            $table->id();
            $table->string('group', 20);
            $table->string('icon', 40)->nullable();
            $table->string('figure', 20)->nullable();
            $table->json('title');
            $table->json('description')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['group', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('highlights');
    }
};
