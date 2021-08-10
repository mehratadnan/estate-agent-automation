<?php
/*
 **************************************************************************************************************
                    _____________#This seeder is about User seeder#_____________


    -run  function  to create default users

 **************************************************************************************************************

Developed by Adnan Mehrat / spechy
*/
namespace Database\Seeders;

use App\Models\SystemVariables;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Phone;
use App\Models\usersCompany;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{

    protected $key = "User";
    protected $key1 = "SubUser";
    /**
     * Run the database seeds.
     *
     * @return void
     */


    public function run()
    {

        User::create([
            'roleID' => 0,
            'fullName' => "Adnan",
            'email' => "Adnan@estate.com",
            'password' => Hash::make("Aa@12345"),
        ]);
    }


}
