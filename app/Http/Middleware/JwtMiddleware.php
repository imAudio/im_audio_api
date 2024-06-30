<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\JsonResponse;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return new JsonResponse(['error' => 'Token is not provided'], 400);
        } catch (TokenExpiredException $e) {
            return new JsonResponse(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return new JsonResponse(['error' => 'Token is invalid'], 401);
        }

        return $next($request);
    }
}
