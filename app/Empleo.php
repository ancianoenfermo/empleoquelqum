<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Empleo extends Model
{
    protected $guarded = [];
    protected $dates = ['fecha'];
   
    public function provincia(){
        return $this->belongsTo(Provincia::class);
    }
   
    public function localidad(){
        return $this->belongsTo(Localidad::class);
    }

}
