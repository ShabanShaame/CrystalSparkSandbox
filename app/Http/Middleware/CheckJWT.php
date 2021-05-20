<?php

namespace App\Http\Middleware;

use App\Services\JWTService;
use Closure;
use \Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CheckJWT
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




        $jwt = JWTService::issueJWT(['kingofbongo'=>1],$request->ip());
       // dd($jwt);
        $payload = JWTService::verifyJwt($request->jwt,$request->ip());

        if (!$payload) $this->failJWT();

        //dd(Session::token());





        return $next($request);
    }

    public function failJWT(){


    dd("jwt verification failed");


    }
}
