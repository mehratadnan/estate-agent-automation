<?php

/*
 **************************************************************************************************************
                    _____________#This controller is about LoginController #_____________


    -login  function to do login


 **************************************************************************************************************

*/

namespace App\Http\Controllers\AuthController;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

use \Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Providers\menusRolePermissionServiceProviders\sidebarMenusServiceProvider;

class LoginController extends Controller
{
    // this function to do login
    public function login(Request $request){
        // checkRequest Func checking requests

        $checkvalidation = $this->checkRequest($request, "login");
        if ($checkvalidation === true) {
            try {
                //select user by user email
                $user = User::where('email', $request->email)->first();
                if (empty($user)) {
                    return $this->response->fail(['message'=>__("response.error")]);
                } else {
                    //Password Check
                    if(Hash::check($request->password, $user->password)){
                        // set payload parameters
                        $payload = [
                            'userID' => $user->userID,
                            'userInfo' => ['fullName'=>$user->fullName],
                            'iat' => time(),
                            "exp" => time() + 60 * 60 * env("APP_EXP_TIME"),
                        ];

                        //encode payload in JWT
                        $jwt = JWT::encode($payload, env("APP_KEY"));

                        //set access token as JWT and refresh token
                        $user->access_token = $jwt;

                        if(!$user->save()){
                            return $this->response->unauthorized();
                        }

                        return $this->response->success(["access_token" => $jwt]);
                    } else {
                        return $this->response->unauthorized();
                    }
                }
            } catch (\Illuminate\Database\QueryException  $exception) {

            }
            return $this->response->fail($exception);
        }else {
            return $this->response->fail(['message'=>$checkvalidation]);
        }
    }



    /**
     * @param $request
     * @param $ctrl
     */
    //checkRequest function to validate inputs
    private function checkRequest($request, $ctrl)
    {
        //Request Cleaning
        foreach ($request->all() as $key => $value) {
            if (!is_array($request[$key])) {
                $request[$key] = trim(strip_tags($request[$key]));
            }
        }
        if ($ctrl === "login") {
            //Request Validator
            $validate = $this->checkValidator($request, [
                'email' => 'required|email|max:50|min:10',
                'password' => 'required|max:50|min:8',
            ]);
        }

        if (!empty($validate) ) {
            return $validate;
        } else {
            return true;
        }
    }

}
