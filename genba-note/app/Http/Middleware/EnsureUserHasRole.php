<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 指定ロール（管理者／作業員）を持つユーザーのみ通すミドルウェア。
 *
 * ルート例: ->middleware('role:admin')
 * 複数指定: ->middleware('role:admin,worker')
 */
class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     * @param  string  ...$roles  許可するロール値（admin / worker）
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        $allowed = collect($roles)
            ->flatMap(fn (string $role) => explode(',', $role))
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->all();

        $current = $user->role instanceof UserRole
            ? $user->role->value
            : (string) $user->role;

        if (! in_array($current, $allowed, true)) {
            abort(403, 'この操作を行う権限がありません。');
        }

        return $next($request);
    }
}
