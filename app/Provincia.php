<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    public function empleos()
    {
        return $this->hasMany(Empleo::class);
    }
    public function localidades()
    {
        return $this->hasMany(Localidad::class);
    }

}
