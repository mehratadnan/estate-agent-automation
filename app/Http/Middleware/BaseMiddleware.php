<?php


namespace App\Http\Middleware;

use App\Providers\JsonServiceProvider;

class BaseMiddleware
{

    protected $response;

    /**
     * Controller constructor.
     */
    public function __construct(){
        $this->response = new JsonServiceProvider();
    }
}
