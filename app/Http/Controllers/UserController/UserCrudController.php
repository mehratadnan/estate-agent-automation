<?php


/*
 **************************************************************************************************************
                    _____________#This controller is about user management#_____________

    -By this controller you can do CRUD operations with users

    -CRUD = Create / Read / Update / Delete

    -store function to store user to database

    -update function  to update one user

    -destroy function  to delete one user

    -show function  to return one user

    -checkRequest function to validate inputs

    -pagination function  to paginate all users with selected inputs


 **************************************************************************************************************

*/


namespace App\Http\Controllers\UserController;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class UserCrudController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse|object
     */

    //this Func will store user to database
    public function store(Request $request)
    {
        // checkRequest Func checking requests
        $checkvalidation = $this->checkRequest($request, "addUser","");
        if ($checkvalidation === true) {
            try {
                // hash user's password
                $password = Hash::make($request->password);

                // edit password with hashed password
                $request['password'] = $password;
                // set type as Admin
                $request['roleID'] = 1;

                //store user typo database
                User::create($request->all());
                return $this->response->success(['message' => __("response.UserStoreSuccess")]);

            } catch (\Illuminate\Database\QueryException  $exception) {
                return $this->response->fail(['message'=>$exception]);
            }
        }
        return $this->response->fail(['message'=>$checkvalidation]);
    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse|object
     */

    //this Func will update one user
    public function update(Request $request , $id)
    {
        // checkRequest Func checking requests
        $checkvalidation = $this->checkRequest($request, "updateUser", $id);
        if ($checkvalidation === true) {
            try {
                // update user password if exist in request
                if($request->has('password')){
                    // hash user's password
                    $password = Hash::make($request->password);
                    // edit password with hashed password
                    $request['password'] = $password;
                }

                // date validate
                if(strlen($request->birthDate) === 10){
                    $request->birthDate = date_format(date_create($request->birthDate),"Y-m-d");
                    if($request->birthDate >= date("Y-m-d")) {
                        return $this->response->fail(['message'=>__("response.dateIsNotMatched")]);
                    }
                }else{
                    $request['birthDate']=null;
                }

                // find user  to update
                $user = User::find($id);
                if (!empty($user)) {
                    $user->update($request->all());
                    return $this->response->success(['message'=>__("response.UserUpdateSuccess")]);

                }
                return $this->response->fail(__("response.UserUpdateFail"));
            } catch (\Illuminate\Database\QueryException  $exception) {
                return $this->response->fail(['message'=>__("response.DatabaseError")]);
            }
        }
        return $this->response->fail(['message'=>$checkvalidation]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse|object
     */

    //this Func will delete one user byID

    public function destroy(Request $request, $id)
    {
        // checkRequest Func checking requests
        $checkvalidation = $this->checkRequest($request, "","");
        if ($checkvalidation === true) {
            // checkRequest Func checking requests
            $id = trim(strip_tags($id));
            if (!empty($id)) {
                try {
                    // find user  to update
                    $user = User::find($id);
                    if (!empty($user)) {
                        $user->update(['tempFreezing' => 1]);
                        return $this->response->success(['message'=>__("response.UserDestroySuccess")]);
                    }
                    return $this->response->fail(__("response.UserDestroyFail"));
                } catch (\Illuminate\Database\QueryException  $exception) {
                    return $this->response->fail(['message'=>__("response.DatabaseError")]);
                }
            }
            return $this->response->fail(['message'=>__("response.error")]);
        }
        return $this->response->fail(['message'=>$checkvalidation]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse|object
     */

    // this Func will return one user with his phone byID
    public function show(Request $request, $id)
    {
        // checkRequest Func checking requests
        $checkvalidation = $this->checkRequest($request, "","");
        if ($checkvalidation === true) {
            $id = trim(strip_tags($id));
            if (!empty($id)) {
                try {
                    // select user to show byID
                    $user = User::find($id);
                    if(!empty($user)){
                        return $this->response->success(["data" => $user]);
                    }
                    return $this->response->fail(['message'=>__("response.UserSelectionFail")]);
                }catch (\Exception  $exception) {
                    return $this->response->fail(['message'=>__("response.DatabaseError")]);
                }
            }
            return $this->response->fail(['message'=>__("response.UserSelectionFail")]);
        }
        return $this->response->fail(['message'=>$checkvalidation]);
    }


    // this Func will paginate all users with selected inputs
    public function pagination(Request $request )
    {
        // checkRequest Func checking requests
        $checkvalidation = $this->checkRequest($request, "listUser","");
        if ($checkvalidation === true) {
            try {
                $users = DB::table('users')
                    ->where('users.tempFreezing','=', $request->tempFreezing)
                    ->where(function ($query) use ($request) {
                        $query->Where('users.email','like','%'.$request->searchValue.'%')
                            ->orWhere('users.fullName','like','%'.$request->searchValue.'%')
                            ->orWhere('users.phone','like','%'.$request->searchValue.'%');
                    })->paginate( $request->perPage,['*'],'users',$request->page)->toArray();

                // re decorator data by removing unneeded keys from json
                $users = $this->removeUnneededDataFromPagination($users);
                $total = $users[1];
                $users = $users[0];

                return $this->response->success(["data" => $users   , "numberOfPage" => $total , "count" => count($users) ]);

            }catch (\Illuminate\Database\QueryException  $exception) {
                return $this->response->fail(__("response.DatabaseError"));
            }
        }else{
            return $this->response->fail($checkvalidation);
        }

    }


    /**
     * @param $request
     * @param $ctrl
     */
    //checkRequest function to validate inputs
    private function checkRequest($request,$ctrl,$id)
    {
        $decoded = $request->get('decoded');
        $user = User::find($decoded->userID);

        if($user->roleID != 0){
            return "response.NoPermission";
        }


        //Request Cleaning
        foreach ($request->all() as $key => $value) {
            if(!is_array($request[$key])){
                $request[$key] = trim(strip_tags($request[$key]));
            }
        }
        if($ctrl === "addUser"){
            //Request Validator
            $validate = $this->checkValidator($request, [
                'email' => 'required|email|max:50|unique:users',
                'fullName' => 'required|max:50|min:5',
                'password' => [
                    'required',
                    'string',
                    'max:64',
                    'min:8',              // must be at least 8 characters in length
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&]/', // must contain a special character
                ],
                'phone' => 'required|max:15|min:9',
            ]);
        }else if($ctrl === "updateUser"){
            //Request Validator
            $validate = $this->checkValidator($request, [
                'email' => 'required|email|max:50|unique:users,email,'.$id.',userID',
                'fullName' => 'required|max:50|min:5',
                'password' => [
                    'required',
                    'string',
                    'max:64',
                    'min:8',              // must be at least 8 characters in length
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&]/', // must contain a special character
                ],
                'phone' => 'required|max:15|min:9',
                'gender' => 'max:1|integer',
                'birthDate' => 'max:10|date',
                'tempFreezing' => 'max:1|integer',
            ]);
        }else if($ctrl === "listUser") {
            //Request Validator
            $validate = $this->checkValidator($request, [
                'page' => 'required|max:25',
                'perPage' => 'required|max:25',
                'searchValue' => 'max:30',
                'tempFreezing' => 'required|max:1'
            ]);
        }

        if (!empty($validate)) {
            return $validate;
        } else {
            return true;
        }
    }




}




