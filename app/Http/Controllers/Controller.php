<?php

namespace App\Http\Controllers;

use App\Providers\CurlServiceProvider;
use App\Providers\JsonServiceProvider;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected $response;
    protected $curlServiceProvider;
    Protected $server;
    Protected $postCode;

    public function __construct(){
        $this->response = new JsonServiceProvider();
        $this->curlServiceProvider = new CurlServiceProvider();
        $this->server = $_ENV['SERVER'];
        $this->postCode = $_ENV['POSTCODE'];
    }

    // re decorator data by removing unneeded keys from json
    protected function removeUnneededDataFromPagination($data): array
    {
        $keysToRemove = ['per_page','path','last_page','last_page_url','links','next_page_url','from','first_page_url','to','current_page','total'];
        $count = $data['last_page'] ;
        foreach ($keysToRemove AS $key){
            unset($data[$key]);
        }
        return [$data['data'],$count];
    }


    /**
     * @param Request $request
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return \Illuminate\Support\MessageBag
     */
    // check if request validate
    protected function checkValidator(
        Request $request,
        array $rules,
        array $messages = [],
        array $customAttributes = [])
    {
        $validator = $this->getValidationFactory()->make(
            $request->all(),
            $rules, $messages,
            $customAttributes
        );

        if ($validator->fails()) {
            return $validator->errors();
        }
    }

}
