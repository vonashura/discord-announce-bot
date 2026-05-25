<?php

namespace App\Console\Commands;

use App\Services\DiscordService;
use Illuminate\Console\Command;

class RegisterDiscordCommands extends Command
{
    protected $signature   = 'discord:register-commands';
    protected $description = 'Registrar los slash commands de Discord (/announce)';

    public function handle(DiscordService $discord): int
    {
        $this->info('Registrando comandos de Discord...');

        $result = $discord->registerCommands();

        if (isset($result['message'])) {
            $this->error('Error de Discord: ' . $result['message']);
            return Command::FAILURE;
        }

        $this->info('Comandos registrados:');
        foreach ((array) $result as $cmd) {
            $this->line('  ✓ /' . ($cmd['name'] ?? '?'));
        }

        return Command::SUCCESS;
    }
}
