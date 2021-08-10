<?php
/*
 **************************************************************************************************************
                    _____________#This controller is about Logout Controller #_____________


    -logout function to do logout


 **************************************************************************************************************


*/

namespace App\Http\Controllers\AuthController;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\usersCompany;
use App\Models\UsersLoginLogout;
use Illuminate\Http\Request;

class  LogoutController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */

    // this function to do logout
    public function logout(Request $request){
        $decoded = $request->get('decoded');
        $user = User::find($decoded->userID);
        if (empty($user)) {
            return $this->response->fail(['message'=>__("response.error")]);
        } else {
            $user->access_token = NULL;
            $user->save();
            return $this->response->success(['message'=>__("response.logOutSuccess")]);
        }
    }
}

