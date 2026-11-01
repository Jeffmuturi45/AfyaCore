<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BranchAccessMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if ($user && !$user->isSuperAdmin() && !$user->branch_id) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account is not assigned to a branch. Contact the administrator.']);
        }

        return $next($request);
    }
}