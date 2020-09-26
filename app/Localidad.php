<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Localidad extends Model
{
    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }
    public function empleos()
    {
        return $this->hasMany(Empleo::class);
    }

}
