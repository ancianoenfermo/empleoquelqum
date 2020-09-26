<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmpleosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        Schema::create('empleos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('titulo');
            $table->text('detalles')->nullable();
            $table->string('vacantes')->nullable();
            $table->string('url')->nullable();
            $table->string('fuente');
            $table->string("provincia");
            $table->string('localidad');
           
            $table->bigInteger('provincia_id')->unsigned()->nullable();
            $table->foreign('provincia_id')->references('id')->on('provincias');

            $table->bigInteger('localidad_id')->unsigned()->nullable();
            $table->foreign('localidad_id')->references('id')->on('localidads');

            $table->bigInteger('fuente_id')->unsigned()->nullable();
            $table->foreign('fuente_id')->references('id')->on('fuentes');

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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('empleos');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); 
        
    }
}
