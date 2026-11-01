<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforceBranchScope
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && ! $user->isSuperAdmin()) {
            // Store branch on the request for controllers to use
            $request->merge(['_branch_id' => $user->branch_id]);
        }

        return $next($request);
    }
}
