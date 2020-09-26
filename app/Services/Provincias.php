<?php
namespace App\Services;
Use App\Provincia;

class Provincias
{
    public function get() {
        $provincias = Provincia::get();
        $provinciasArray[''] = 'Todas';
        foreach ($provincias as $provincia) {
            $provinciasArray[$provincia->id] = $provincia->name;
        }
        return $provinciasArray;
    }

}