<?php

namespace App\Http\Controllers;

use App\Services\DiscordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DiscordService $discord) {}

    public function index(): View
    {
        return view('dashboard.index');
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type'        => 'required|in:general,fortnite',
            'color'       => 'required|string',
            'channel'     => 'required|in:announcement,fortnite,webhook',
            'webhook_url' => 'required_if:channel,webhook|nullable|url',
            // General
            'title'       => 'required_if:type,general|nullable|string|max:256',
            'message'     => 'required_if:type,general|nullable|string|max:2000',
            // Fortnite
            'mode'          => 'required_if:type,fortnite|nullable|string',
            'modalidad'     => 'required_if:type,fortnite|nullable|string',
            'clasificatoria'=> 'required_if:type,fortnite|nullable|in:si,no',
            'region'        => 'required_if:type,fortnite|nullable|string',
            'password'      => 'required_if:type,fortnite|nullable|string|max:50',
        ]);

        $embed = $validated['type'] === 'general'
            ? $this->discord->buildGeneralEmbed(
                $validated['title'],
                $validated['message'],
                $validated['color']
            )
            : $this->discord->buildFortniteEmbed(
                $validated['mode'],
                $validated['modalidad'],
                $validated['clasificatoria'],
                $validated['region'],
                $validated['password'],
                $validated['color']
            );

        if ($validated['channel'] === 'webhook' && !empty($validated['webhook_url'])) {
            $this->discord->sendWebhook($validated['webhook_url'], $embed);
        } else {
            $isFortnite = $validated['channel'] === 'fortnite';
            $channelId  = $isFortnite
                ? $this->discord->getFortniteChannelId()
                : $this->discord->getAnnouncementChannelId();

            if (!$channelId) {
                return back()->withErrors([
                    'channel' => 'Canal no configurado. Ve a /settings y guarda el Channel ID correspondiente.',
                ]);
            }

            $roleId = $isFortnite
                ? $this->discord->getFortniteRoleId()
                : $this->discord->getAnnounceRoleId();
            $this->discord->sendEmbed($channelId, $embed, $roleId ? "<@&{$roleId}>" : null);
        }

        return back()->with('success', '✅ Anuncio enviado correctamente.');
    }
}
