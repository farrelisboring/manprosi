<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
            }

            return new RedirectResponse(route('login'));
        }

        if ($user->isAdministrator()) {
            return $next($request);
        }

        $normalizedRoles = collect($roles)
            ->map(static fn (string $role): string => trim(mb_strtolower($role)))
            ->filter()
            ->all();

        if (in_array($user->role?->value, $normalizedRoles, true)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return new JsonResponse(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        abort(Response::HTTP_FORBIDDEN);
    }
}
