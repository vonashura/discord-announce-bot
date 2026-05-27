<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discord_users', function (Blueprint $table) {
            $table->string('discord_id')->primary();
            $table->string('username');
            $table->string('avatar')->nullable();
            $table->boolean('approved')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discord_users');
    }
};
