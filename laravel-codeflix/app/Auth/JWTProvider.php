<?php

namespace CodeFlix\Auth;

use Dingo\Api\Auth\Provider\Authorization;
use Dingo\Api\Routing\Route;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWT;

class JWTProvider extends Authorization
{
    private $jwt;

    public function __construct(JWT $jwt)
    {
        $this->jwt = $jwt;
    }

    public function getAuthorizationMethod()
    {
        return 'bearer';
    }

    public function authenticate(Request $request, Route $route)
    {
        try{
            return \Auth::guard('api')->authenticate();
        }catch (AuthenticationException $exception){
            $this->refreshToken();
            return \Auth::guard('api')->user();
        }
    }

    protected function refreshToken()
    {
        $token = $this->jwt->parseToken()->refresh();
        $this->jwt->setToken($token);
    }
}