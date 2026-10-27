<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * People presented on the about page.
     */
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->json('role');
            $table->json('bio')->nullable();
            $table->foreignId('photo_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('position')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
