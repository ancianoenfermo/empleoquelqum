@extends('layouts.app')
@section('content') 
   <div class="container mt-2">
       
        <div class="row">
            <div class="col-md-4">
                <div class="card mt-4 border-0 ">
                    <label class="pl-2 quelqum-text">Provincia</label>
                   
                    
                    <select class="form-control" id="provincia" name = "provincia">
                        
                        <option value="">Todas</option>
                        @foreach ($provincias as $provincia)        
                            <option value="{{$provincia->id}}"
                                @isset($oldProvincia) 
                                    @if($oldProvincia == $provincia->id)
                                        selected
                                    @endif
                                @endisset
                                >
                                {{$provincia->name}} 
                            </option>
                        @endforeach

                    </select>
                </div>
            </div>  
            
            


            
            <div class="col-md-4 ">    
                <div class="card mt-4 border-0 ">
                    <label class="pl-2  quelqum-text" >Localidad</label>
                    
                    <select id="localidad" name = "localidad" class="form-control">
                        
                        <option value="">Todas</option>
                            @foreach($localidades as $localidad)
                                <option value="{{$localidad->id}}"
                                    @isset($oldLocalidad) 
                                        @if($oldLocalidad == $localidad->id)
                                            selected
                                        @endif
                                    @endisset
                                    >{{$localidad->name}} 
                                </option>
                            @endforeach
                    </select>
                </div>

            </div>

            <div class="col-md-4">    
                <div class="card mt-4 border-0 ">
                    <label class="pl-2 quelqum-text">Fuente</label>
                    <select id="fuente" name = "fuente" class="form-control">
                        <option value="">Todas</option>
                            @foreach($fuentes as $fuente)
                                <option value="{{$fuente->id}}"
                                    @isset($oldFuente) 
                                        @if($oldFuente == $fuente->id)
                                            selected
                                        @endif
                                    @endisset
                                    >{{$fuente->name}} 
                                </option>
                            @endforeach
                    </select>
                </div>

            </div> 
            
        </div>  
        <div class="row">
            <div class="col-md-10 mt-2">
                <p class="badge quelqum-headers text-white d-inline ">Total ofertas:</p><p class="d-inline">   </p><p class="badge quelqum-headers text-white d-inline">{{$ofertas->total()}}</p>
                <a 
                @if ($oldProvincia == '' AND $oldLocalidad == '' AND $oldFuente == '')
                    hidden
                @endif
                
                
                
                href="#"> <i class="fas fa-filter quelqum-text small " ></i></a>
            </div>
            
        </div>

    </div>
 
    <hr>

    <div class="container">
        @foreach($ofertas as $oferta)
            <div class = "card mt-4 border">
            
                    <div class="row align-items-center no-gutters">
                        <div class="col-md-3 pl-3">
                            <span class="badge badge-light">Publicado {{$oferta->fecha->diffForHumans()}} <small>({{$oferta->fecha->format('d M yy')}})</small></span>
                        </div>
                        
                        <div class="col-md-9 pr-3 text-right">
                            <a href="{{$oferta->url}}"> <small>Fuente: {{$oferta->fuente}}</small></a>
                        </div>
                    </div>
                    
                    <div class="row align-items-center no-gutters">
                        <div class="col-7 pl-3 pt-2">
                            <span class ="font-weight-bold">{{$oferta->titulo}}</span>
                        </div>
                        <div class="col-5 text-right pr-3">
                            <span class=" text-secondary">{{$oferta->localidad}}</span> <span><small>({{$oferta->provincia}})</small></span>
                        </div>
                    </div>
                   
                    <div class="row align-items-center no-gutters">
                        <div class="col-12 pl-3 pt-2 pb-3">
                            <span class ="font-italic">{!!$oferta->detalles!!}</span>
                        </div>
                    </div>
            
            </div> 
        @endForeach  
        <br>
        <span class="pagination  justify-content-center">{{$ofertas->links()}}</span>  
    </div>
   

@endSection

@section('script')
<script src="/js/users/empleoPrivado.js"></script>

@endSection

