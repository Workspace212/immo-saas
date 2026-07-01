<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasAgency
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($user->agency_id !== null || $user->hasRole(['Super Admin', 'super_admin'])) {
            return $next($request);
        }

        return new JsonResponse([
            'message' => 'User must belong to an agency.',
        ], Response::HTTP_FORBIDDEN);
    }
}
