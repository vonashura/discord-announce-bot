# Discord Announce Bot - Setup Script
# Ejecutar despues de instalar PHP 8.2+ y Composer
# Uso: .\setup.ps1

$ErrorActionPreference = "Stop"
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path

Write-Host "`n🤖 Discord Announce Bot - Setup`n" -ForegroundColor Cyan

# ── Verificar PHP ──────────────────────────────────────────────────
try {
    $phpVersion = php --version 2>&1 | Select-Object -First 1
    Write-Host "✓ PHP: $phpVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ PHP no encontrado. Instala PHP 8.2+ desde:" -ForegroundColor Red
    Write-Host "  https://windows.php.net/download/" -ForegroundColor Yellow
    Write-Host "  O con winget: winget install PHP.PHP.8.3" -ForegroundColor Yellow
    exit 1
}

# ── Verificar Composer ─────────────────────────────────────────────
try {
    $composerVersion = composer --version 2>&1 | Select-Object -First 1
    Write-Host "✓ Composer: $composerVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ Composer no encontrado. Instala desde:" -ForegroundColor Red
    Write-Host "  https://getcomposer.org/download/" -ForegroundColor Yellow
    exit 1
}

# ── Verificar Node.js ──────────────────────────────────────────────
try {
    $nodeVersion = node --version 2>&1
    Write-Host "✓ Node.js: $nodeVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ Node.js no encontrado. Instala desde:" -ForegroundColor Red
    Write-Host "  https://nodejs.org/  O con winget: winget install OpenJS.NodeJS" -ForegroundColor Yellow
    exit 1
}

Set-Location $scriptDir

# ── Composer install ───────────────────────────────────────────────
Write-Host "`n📦 Instalando dependencias PHP (composer install)..." -ForegroundColor Cyan
composer install --no-interaction --prefer-dist

# ── .env ──────────────────────────────────────────────────────────
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "✓ Archivo .env creado desde .env.example" -ForegroundColor Green
} else {
    Write-Host "✓ .env ya existe" -ForegroundColor Yellow
}

# ── App key ───────────────────────────────────────────────────────
Write-Host "`n🔑 Generando APP_KEY..." -ForegroundColor Cyan
php artisan key:generate --ansi

# ── NPM install + build ────────────────────────────────────────────
Write-Host "`n📦 Instalando dependencias JS (npm install)..." -ForegroundColor Cyan
npm install

Write-Host "`n🔨 Compilando assets Tailwind..." -ForegroundColor Cyan
npm run build

# ── Done ───────────────────────────────────────────────────────────
Write-Host "`n✅ Setup completado!" -ForegroundColor Green
Write-Host @"

Proximos pasos:
──────────────────────────────────────────────────────
1. Edita .env con tus credenciales de Discord:
     DISCORD_BOT_TOKEN         → Token del bot
     DISCORD_APPLICATION_ID    → ID de la aplicacion
     DISCORD_PUBLIC_KEY        → Clave publica (Interactions)
     DISCORD_GUILD_ID          → ID del servidor (para dev)
     DISCORD_ANNOUNCEMENT_CHANNEL_ID
     DISCORD_FORTNITE_CHANNEL_ID

2. Registra el comando /announce en Discord:
     php artisan discord:register-commands

3. Inicia el servidor web:
     php artisan serve

4. Para recibir interacciones de Discord en local, usa ngrok:
     ngrok http 8000
   Y pon la URL en Discord Developer Portal:
     Applications → tu app → Interactions Endpoint URL:
     https://<tu-ngrok>.ngrok-free.app/api/discord/interactions

5. En Discord Developer Portal, activa:
     Bot → MESSAGE CONTENT INTENT
     OAuth2 → bot scope + applications.commands
──────────────────────────────────────────────────────
"@ -ForegroundColor White
