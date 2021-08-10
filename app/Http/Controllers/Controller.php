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

    public function __construct(){
        $this->response = new JsonServiceProvider();
        $this->curlServiceProvider = new CurlServiceProvider();
        $this->server = $_ENV['SERVER'];
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
