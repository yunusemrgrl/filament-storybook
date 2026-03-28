<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdminBuilder
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $panel = Filament::getPanel('admin');

        if (
            $user &&
            $panel &&
            (! $user instanceof FilamentUser || $user->canAccessPanel($panel))
        ) {
            return $next($request);
        }

        return redirect()->guest($panel?->getLoginUrl() ?? url('/admin/login'));
    }
}
