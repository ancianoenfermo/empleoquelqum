<?php

namespace App\Http\Controllers;

use App\Empleo;
use App\Fuente;
use App\Localidad;
use App\Provincia;
use Illuminate\Http\Request;

class EmpleoPrivadoController extends Controller
{
    public function index()
    { 
        $oldProvincia = request('provincia_id');
        $oldLocalidad = request('localidad_id');
        $oldFuente = request(('fuente_id'));

        $ofertas = new Empleo;
    
        
        $queries = [];
        $columns = [
            'provincia_id','localidad_id','fuente_id'
        ];
        foreach ($columns as $column) {
            if(request()->has($column)) {
                if (request($column)!=0) {
                    $ofertas = $ofertas
                    ->where($column, request($column));
                    $queries[$column] = request($column);
                }
            } 
        }
        $provincias = Provincia::get();

        
        if($oldProvincia == null || $oldProvincia == '') {
            $localidades = Localidad::orderBy('provincia_id', 'ASC')
            ->orderBy('name', 'ASC')
            ->get();
        } else {
            $localidades = Localidad::where('provincia_id','=',$oldProvincia)->get();
           
        }
        $fuentes = Fuente::get();
        
        $ofertas = $ofertas->orderby('fecha','DESC')->paginate()->appends($queries);
       
        return view('empleoPrivado',compact('ofertas','localidades','provincias','oldProvincia','oldFuente','oldLocalidad','fuentes'));
        
    }
    

   public function getLocalidades($id) {
        
       return Localidad::where('provincia_id',$id)->get();
        
    }
    

    /* if(request()->has('provincia_id')) {
        $ofertas = Empleo::where('provincia_id',request('provincia_id'))
        ->paginate()
        ->appends('provincia_id', request('provincia_id')); 
    } else {
        $ofertas = Empleo::paginate();
    }
    
    $localidades = Localidad::all(); */



    /* public function getLocalidades(Request $request) {
        
        if ($request->ajax()) {
            $localidades = Localidad::where('provincia_id', $request->provincia_id)->get();
            foreach ($localidades as $localidad) {
                $localidadesArray[$localidades->id] = $localidades->name;
            }
           
            return response()->json($localidadesArray);
        }
        
    } */





}
