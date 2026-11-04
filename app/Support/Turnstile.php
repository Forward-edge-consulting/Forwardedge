<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Cloudflare Turnstile, shared by every public form. It is only enforced when both keys are set,
 * so a half-configured install never locks visitors out.
 */
class Turnstile
{
    public static function enabled(): bool
    {
        return filled(config('services.turnstile.site_key')) && filled(config('services.turnstile.secret'));
    }

    public static function passes(Request $request): bool
    {
        if (!self::enabled()) {
            return true;
        }

        $token = (string) $request->input('cf-turnstile-response');
        if ($token === '') {
            return false;
        }

        try {
            $result = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret'   => config('services.turnstile.secret'),
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            return (bool) $result->json('success');
        } catch (\Throwable) {
            // If Cloudflare is unreachable, do not turn visitors away; honeypot and throttling still apply.
            return true;
        }
    }

    public const FAILED_MESSAGE = 'We could not verify you are human. Please try again.';
}
