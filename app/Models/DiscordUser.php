<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordUser extends Model
{
    protected $primaryKey = 'discord_id';
    protected $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = ['discord_id', 'username', 'avatar', 'approved', 'is_admin'];

    protected $casts = [
        'approved' => 'boolean',
        'is_admin' => 'boolean',
    ];

    public function avatarUrl(): string
    {
        if ($this->avatar) {
            return "https://cdn.discordapp.com/avatars/{$this->discord_id}/{$this->avatar}.png?size=64";
        }
        return 'https://cdn.discordapp.com/embed/avatars/0.png';
    }
}
