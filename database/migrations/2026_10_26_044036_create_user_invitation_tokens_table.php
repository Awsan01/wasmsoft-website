<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tokens of the "invitations" password broker. Kept apart from password
     * reset tokens, so a short-lived reset link can never be used as a
     * longer-lived invitation (or the other way round).
     */
    public function up(): void
    {
        Schema::create('user_invitation_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_invitation_tokens');
    }
};
