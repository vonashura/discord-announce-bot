<?php

namespace App\Http\Controllers;

use App\Models\DiscordUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function users(): View
    {
        $users = DiscordUser::orderByDesc('is_admin')->orderBy('username')->get();
        return view('admin.users', compact('users'));
    }

    public function approve(string $id): RedirectResponse
    {
        DiscordUser::where('discord_id', $id)->update(['approved' => true]);
        return back()->with('success', 'Usuario aprobado.');
    }

    public function revoke(string $id): RedirectResponse
    {
        // Cannot revoke admins
        $user = DiscordUser::find($id);
        if ($user?->is_admin) {
            return back()->withErrors(['action' => 'No puedes revocar a un administrador.']);
        }

        DiscordUser::where('discord_id', $id)->update(['approved' => false]);
        return back()->with('success', 'Acceso revocado.');
    }
}
