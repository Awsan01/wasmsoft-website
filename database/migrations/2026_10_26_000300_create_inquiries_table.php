<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Messages sent through the contact form. Only what the visitor typed is
     * stored (no IP address or browser data); rows are deleted after the
     * retention period (config wasmsoft.contact.retention_days).
     */
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20)->default('new');
            $table->string('locale', 5);
            $table->string('name', 120);
            $table->string('email', 254);
            $table->string('phone', 40)->nullable();
            $table->string('company', 150)->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message');
            $table->text('internal_note')->nullable();
            $table->timestamp('consented_at');
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
