<?php


/*
 **************************************************************************************************************
                    _____________#This controller is about Assignation management#_____________

    -By this controller you can do CRUD operations with users

    -CRUD = Create / Read / Update / Delete

    -store function to store Assignation to database

    -update function  to update one Assignation

    -destroy function  to delete one Assignation

    -show function  to return one Assignation

    -checkRequest function to validate inputs

    -pagination function  to paginate all Assignation with selected inputs


 **************************************************************************************************************

*/


namespace App\Http\Controllers\AssignmentsController;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AssignmentsCrudController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse|object
     */

    //this Func will store Appointment to database
    public function store(Request $request)
    {
        // checkRequest Func checking requests
        $checkvalidation = $this->checkRequest($request, "addAppointment","");
        if ($checkvalidation === true) {
            try {
                $response = $this->extractAddress($request->address);
                if($response === 0){
                    return $this->response->fail(['message'=> __("response.InvalidPostcode")]);
                }
                $request['longAddress'] = $response;

                $response = $this->extractDistanceAndDuration($request->address,$this->postCode,$request->time);
                $request['distance'] = $response['distance'];
                $request['time'] = $response['time'];
                $request['checkoutTime'] = $response['checkoutTime'];
                $request['returnTime'] = $response['returnTime'];

                Appointment::create($request->all());

                return $this->response->success(['message' => __("response.AppointmentStoreSuccess")]);

            } catch (\Illuminate\Database\QueryException  $exception) {
                return $this->response->fail(['message'=> $exception]);
            }
        }
        return $this->response->fail(['message'=>$checkvalidation]);
    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse|object
     */

    //this Func will update one Appointment
    public function update(Request $request , $id)
    {
        // checkRequest Func checking requests
        $checkvalidation = $this->checkRequest($request, "updateAppointment", $id);
        if ($checkvalidation === true) {
            try {
                // find Appointment  to update
                $appointment = Appointment::find($id);
                if (!empty($appointment)) {
                    $appointment->update($request->all());
                    return $this->response->success(['message'=>__("response.AppointmentUpdateSuccess")]);
                }
                return $this->response->fail(__("response.AppointmentUpdateFail"));
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

    //this Func will delete one Appointment byID

    public function destroy(Request $request, $id)
    {
        // checkRequest Func checking requests
        $checkvalidation = $this->checkRequest($request, "","");
        if ($checkvalidation === true) {
            // checkRequest Func checking requests
            $id = trim(strip_tags($id));
            if (!empty($id)) {
                try {
                    // find Appointment  to update
                    $appointment = Appointment::find($id);
                    if (!empty($appointment)) {
                        $appointment->update(['tempFreezing' => 1]);
                        return $this->response->success(['message'=>__("response.AppointmentDestroySuccess")]);
                    }
                    return $this->response->fail(__("response.AppointmentDestroyFail"));
                } catch (\Illuminate\Database\QueryException  $exception) {
                    return $this->response->fail(['message'=>__("response.DatabaseError")]);
                }
            }
            return $this->response->fail(['message'=>__("response.AppointmentSelectionFail")]);
        }
        return $this->response->fail(['message'=>$checkvalidation]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse|object
     */

    // this Func will return one Appointment with his phone byID
    public function show(Request $request, $id)
    {
        // checkRequest Func checking requests
        $checkvalidation = $this->checkRequest($request, "","");
        if ($checkvalidation === true) {
            $id = trim(strip_tags($id));
            if (!empty($id)) {
                try {
                    // select Appointment to show byID
                    $appointment = Appointment::find($id);
                    if(!empty($appointment)){
                        return $this->response->success(["data" => $appointment]);
                    }
                    return $this->response->fail(['message'=>__("response.AppointmentSelectionFail")]);
                }catch (\Exception  $exception) {
                    return $this->response->fail(['message'=>__("response.DatabaseError")]);
                }
            }
            return $this->response->fail(['message'=>__("response.AppointmentSelectionFail")]);
        }
        return $this->response->fail(['message'=>$checkvalidation]);
    }


    // this Func will paginate all Appointments with selected inputs
    public function pagination(Request $request )
    {
        // checkRequest Func checking requests
        $checkvalidation = $this->checkRequest($request, "listAppointment","");
        if ($checkvalidation === true) {
            try {
                $appointments = DB::table('appointments');
                if(isset($request->date)  && !empty($request->date)  ){
                    $appointments->where('appointments.date','=', $request->date );
                }
                if(isset($request->userID)  && !empty($request->userID)  ){
                    $appointments->where('appointments.userID','=', $request->userID );
                }
                $appointments->where('appointments.tempFreezing','=', $request->tempFreezing)
                    ->where(function ($query) use ($request) {
                        $query->Where('appointments.email','like','%'.$request->searchValue.'%')
                            ->orWhere('appointments.surname','like','%'.$request->searchValue.'%')
                            ->orWhere('appointments.name','like','%'.$request->searchValue.'%')
                            ->orWhere('appointments.phone','like','%'.$request->searchValue.'%');
                    });

                $appointments = $appointments->paginate( $request->perPage,['*'],'appointments',$request->page)->toArray();

                // re decorator data by removing unneeded keys from json
                $appointments = $this->removeUnneededDataFromPagination($appointments);
                $total = $appointments[1];
                $appointments = $appointments[0];

                return $this->response->success(["data" => $appointments   , "numberOfPage" => $total , "count" => count($appointments) ]);

            }catch (\Illuminate\Database\QueryException  $exception) {
                return $this->response->fail(["asd"=>$exception]);
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
        //Request Cleaning
        foreach ($request->all() as $key => $value) {
            if(!is_array($request[$key])){
                $request[$key] = trim(strip_tags($request[$key]));
            }
        }
        if($ctrl === "addAppointment"){
            //Request Validator
            $validate = $this->checkValidator($request, [
                'email' => 'required|email|max:50',
                'name' => 'required|max:50|min:5',
                'surname' => 'required|max:50|min:5',
                'phone' => 'required|max:15|min:9',
                'date' => 'max:10|date|date_format:Y-m-d|after_or_equal:today',
                'time' => 'required|date_format:H:i|after:11:00|before:20:00',
                'address' => 'required|max:50|min:3',
            ]);
        }else if($ctrl === "updateAppointment"){
            //Request Validator
            $validate = $this->checkValidator($request, [
                'email' => 'required|email|max:50',
                'name' => 'required|max:50|min:5',
                'surname' => 'required|max:50|min:5',
                'phone' => 'required|max:15|min:9',
                'date' => 'max:10|date|date_format:Y-m-d|after_or_equal:today',
                'time' => 'required|date_format:H:i|after:11:00|before:20:00',
                'address' => 'required|max:50|min:3',
            ]);
        }else if($ctrl === "listAppointment") {
            //Request Validator
            $validate = $this->checkValidator($request, [
                'page' => 'required|max:25',
                'perPage' => 'required|max:25',
                'searchValue' => 'max:30',
                'tempFreezing' => 'required|max:1',
                'date' => 'max:10|min:10',
                'userID' => 'integer|max:30',
            ]);
        }

        if (!empty($validate)) {
            return $validate;
        } else {
            return true;
        }
    }


    // this function to send get request
    public function getRequest( $url){
        $response = $this->curlServiceProvider->get($this->apiPostCode.$url);
        return $response;
    }

    // this function to send get request
    public function extractAddress($url)
    {
        $response = json_decode($this->getRequest($url));
        if($response->status !== 200){
            return 0;
        }
        $response = $response->result;
        $longAdress = $response->country.','.$response->admin_district.','.$response->admin_county.','.$response->admin_ward;
        return $longAdress;
    }


    // this function to send get request
    public function extractDistanceAndDuration($origin,$destination,$time)
    {
        $url="https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$origin."&destinations=".$destination."&sensor=false&key=".$this->apiGoogleKey;
        $response = json_decode(file_get_contents($url));

        list($hours, $minutes) = explode(':', $time, 2);
        $seconds = $minutes * 60 + $hours * 3600;

        $distance = $response->rows[0]->elements[0]->distance->text;
        $duration = $response->rows[0]->elements[0]->duration->value;

        $checkSeconds = $seconds - $duration;
        $returnSeconds = $seconds + 3600 + $duration;

        $response=[
            "distance"=> $distance,
            "duration"=> $duration,
            "time"=> gmdate("H:i", $seconds),
            "checkoutTime"=> gmdate("H:i", $checkSeconds),
            "returnTime"=> gmdate("H:i", $returnSeconds),
        ];
        return $response;
    }


}




