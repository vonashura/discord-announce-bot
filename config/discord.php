<?php

return [
    'token'                    => env('DISCORD_BOT_TOKEN'),
    'application_id'           => env('DISCORD_APPLICATION_ID'),
    'client_secret'            => env('DISCORD_CLIENT_SECRET'),     // OAuth2 secret
    'public_key'               => env('DISCORD_PUBLIC_KEY'),
    'guild_id'                 => env('DISCORD_GUILD_ID'),
    'admin_id'                 => env('DISCORD_ADMIN_ID'),          // tu Discord user ID
    'announcement_channel_id'  => env('DISCORD_ANNOUNCEMENT_CHANNEL_ID'),
    'fortnite_channel_id'      => env('DISCORD_FORTNITE_CHANNEL_ID'),
    'announce_role_id'         => env('DISCORD_ANNOUNCE_ROLE_ID'),
    'fortnite_role_id'         => env('DISCORD_FORTNITE_ROLE_ID'),
];
