<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;

use \Firebase\JWT\JWT;

class AuthMiddleware extends BaseMiddleware
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
        //Token Explode
        $authorizationHeader = explode(' ',$request->header('Authorization'));
        $head = isset($authorizationHeader[0]) ? $authorizationHeader[0]: false;
        $jwt = isset($authorizationHeader[1]) ? $authorizationHeader[1]: false;
        if(!$head || !$jwt){
            return $this->response->unauthorized();
        }

        try{
            $secretKey = env('APP_KEY');
            $decoded = JWT::decode($jwt, $secretKey, array('HS256'));
            $request->attributes->add(['decoded' => $decoded, 'jwt' => $jwt]);
            //DB Token Valid Control
            $user = User::find($decoded->userID)->first();
            if(empty($user->access_token)){
                return $this->response->unauthorized();
            }
            return $next($request);
        }catch (\Exception $e) {
            return $this->response->unauthorized();
        }
    }
}
