<?php

use App\Http\Controllers\DiscordController;
use Illuminate\Support\Facades\Route;

// Discord Interactions Endpoint
// Configurar en: Discord Developer Portal → Applications → [tu app] → Interactions Endpoint URL
// URL: https://tu-dominio.com/api/discord/interactions
Route::post('/discord/interactions', [DiscordController::class, 'handle'])
    ->middleware('discord.verify');
