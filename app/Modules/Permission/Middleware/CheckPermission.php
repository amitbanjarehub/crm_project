<?php

namespace App\Modules\Permission\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {
        $user = $request->user();

        if (
            !$user ||
            !method_exists($user, 'hasPermission') ||
            !$user->hasPermission($permission)
        ) {
            abort(
                403,
                'You are not authorized to access this section.'
            );
        }

        return $next($request);
    }
}