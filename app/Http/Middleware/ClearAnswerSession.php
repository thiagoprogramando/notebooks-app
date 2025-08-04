<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClearAnswerSession {

    public function handle(Request $request, Closure $next): Response {

        if (!$request->is('notebook/answer*')) {
            session()->forget('answer');
        }

        return $next($request);
    }
}
