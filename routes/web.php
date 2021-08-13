<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/



$router->group(['prefix' => '/api'], function () use ($router) {
    $router->group(['prefix' => '/auth'], function () use ($router) {
        $router->post('/login', 'AuthController\LoginController@login');
        $router->group(['middleware' => 'auth'], function () use ($router) {
            $router->get('/logout', 'AuthController\LogoutController@logout');
        });
    });

    // add Appointments ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $router->POST('/customer/appointment/add', 'AppointmentsController\AppointmentsCrudController@store');


    $router->group(['middleware' => 'auth'], function () use ($router) {
        // AppointmentsController ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $router->group(['prefix' => '/appointment'], function () use ($router) {
            $router->POST('/pagination', 'AppointmentsController\AppointmentsCrudController@pagination');
            $router->POST('/add', 'AppointmentsController\AppointmentsCrudController@store');
            $router->POST('/update/{id}', 'AppointmentsController\AppointmentsCrudController@update');
            $router->delete('/delete/{id}', 'AppointmentsController\AppointmentsCrudController@destroy');
            $router->get('/select/{id}', 'AppointmentsController\AppointmentsCrudController@show');
            $router->POST('/update/assignment/{id}', 'AppointmentsController\AppointmentsCrudController@assignment');
            $router->get('/update/assignment/done/{id}', 'AppointmentsController\AppointmentsCrudController@done');

        });


        // UserController ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $router->group(['prefix' => '/user'], function () use ($router) {
            $router->POST('/pagination', 'UserController\UserCrudController@pagination');
            $router->POST('/add', 'UserController\UserCrudController@store');
            $router->POST('/update/{id}', 'UserController\UserCrudController@update');
            $router->delete('/delete/{id}', 'UserController\UserCrudController@destroy');
            $router->get('/select/{id}', 'UserController\UserCrudController@show');
        });
    });

});
