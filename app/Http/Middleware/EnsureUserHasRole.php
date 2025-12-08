<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * 指定されたロールをユーザーが持っているか確認する
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 認証されていない場合はログインページへリダイレクト
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // ユーザーのロールが指定されたロールと一致しない場合は403エラー
        if ($request->user()->role !== $role) {
            abort(403, 'Access Denied');
        }

        return $next($request);
    }
}
