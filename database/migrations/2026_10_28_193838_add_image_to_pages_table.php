<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A picture per page, from the media library: the wide photo at the top
     * of the about, partnerships and custom pages. Deleting the picture from
     * the library (which the library refuses while a page uses it) leaves
     * the page without one.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->foreignId('image_id')->nullable()->after('template')->constrained('media')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('image_id');
        });
    }
};
