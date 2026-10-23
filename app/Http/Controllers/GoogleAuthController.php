<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public static function enabled(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    public function redirect()
    {
        abort_unless(self::enabled(), 404);

        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(Request $request)
    {
        abort_unless(self::enabled(), 404);

        try {
            $google = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('login')->with('error', 'Connexion Google annulée ou impossible. Réessayez.');
        }

        $email = mb_strtolower((string) $google->getEmail());
        $verified = (bool) ($google->user['email_verified'] ?? $google->user['verified_email'] ?? false);

        // Sans e-mail vérifié par Google, impossible de rattacher le compte en toute sécurité
        if ($email === '' || ! $verified) {
            return redirect()->route('login')->with('error', 'Votre adresse Google doit être vérifiée pour vous connecter.');
        }

        $user = User::where('google_id', $google->getId())->first()
            ?? User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            User::create([
                'name'       => $google->getName() ?: $email,
                'email'      => $email,
                'google_id'  => $google->getId(),
                'avatar_url' => $google->getAvatar(),
                'role'       => 'accueil',
                'is_active'  => false,
            ]);

            return redirect()->route('login')->with('status', 'Demande envoyée ! Le patron doit valider votre compte avant votre première connexion.');
        }

        $user->forceFill([
            'google_id'  => $user->google_id ?: $google->getId(),
            'avatar_url' => $google->getAvatar() ?: $user->avatar_url,
        ])->save();

        if ($user->isPending()) {
            return redirect()->route('login')->with('status', 'Votre demande est en cours de validation par le patron.');
        }

        if (! $user->is_active) {
            return redirect()->route('login')->with('error', 'Compte désactivé. Contactez le patron.');
        }

        // Le mécanicien arrive directement dans son application mobile
        if ($user->isMecanicien()) {
            return MecanicienAppController::redirectToApp($user);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
