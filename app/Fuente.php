<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fuente extends Model
{
    public function empleos()
    {
        return $this->hasMany(Empleo::class);
    }
}
