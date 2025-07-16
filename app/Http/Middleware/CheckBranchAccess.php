<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckBranchAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Assumes the route has a 'branch_id' parameter
        $branchId = $request->route('branch_id');

        if ($user && $user->role === 'admin' && $user->branch_id != $branchId) {
            abort(403, 'Unauthorized branch access.');
        }

        return $next($request);
    }
}
