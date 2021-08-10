<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->bigIncrements('appointmentID');
            $table->tinyInteger('roleID')
                ->comment = 'admin 0 / user 1';
            $table->string("name",50);
            $table->string("surname",50);
            $table->string("phone",15);
            $table->string("email",50);

            $table->date("date");
            $table->string("address",10);
            $table->string("distance",10);
            $table->date("checkoutTime",10);
            $table->date("returnTime ",10);



            $table->string("access_token",1000)->nullable();
            $table->tinyInteger("tempFreezing")->default(0)
                ->comment = 'activate 0 / deactivate 1 ';
            $table->tinyInteger("gender")->default(0)
                ->comment = 'Not Chosen 0 / Man 1 / Woman 2 / dont want to choose 3';
            $table->date('birthDate')->nullable();
            $table->timestamps();

        });



    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
