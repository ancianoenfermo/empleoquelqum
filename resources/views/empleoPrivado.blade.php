@extends('layouts.app')
@section('content') 
   <div class="container mt-2">
       
        
        <div class="row mb-2">
            <div class="col-md-12 mt-2">
                <p class="d-inline font-italic">Total ofertas: </p><p class= "d-inline font-weight-bold ">{{$ofertas->total()}}</p>
                <a 
                @if ($oldProvincia == '' AND $oldLocalidad == '' AND $oldFuente == '')
                    hidden
                @endif
                
                href="#"> <i class="fas fa-filter quelqum-text small " ></i></a>
            </div>  
        </div>

    </div>
 
    

    <div class="container ">
        @foreach($ofertas as $oferta)
            <div class = "card mt-4 mb-4 border-top-0 border-bottom-0 border-right-0 border-3">
                
                    <div class="row align-items-center no-gutters quelqum-headers2">
                        <div class="col-md-4 pl-3 pt-2 pb-2">
                            <span class="badge quelqum-headers text-white">Publicado {{$oferta->fecha->diffForHumans()}} <small>({{$oferta->fecha->format('d M yy')}})</small>
                                 
                            </span>
                        </div>
                        <div class="col-md-4 pr-3 text-right">
                            <a  class ="quelqum-text" href="{{$oferta->url}}"> <small></small>Fuente: {{$oferta->fuente}}</small></a>
                        </div>

                        
                        <div class="col-md-4 pr-2 text-right">
                            <small class="font-weight-bold">{{$oferta->localidad}}</small> <span><small>({{$oferta->provincia}})</small></span>
                        </div>
                       
                        
                        
                    </div>
                    
                    <div class="row align-items-center no-gutters">
                        <div class="col-10 pl-3 pt-3">
                            <h3 class = "quelqum-h2 font-weight-bold ">{{$oferta->titulo}}</h3>
                        </div>
                        <div class="col-2 text-right pr-3 align-items-center ">
                            <span class=""><small>Vacantes: </small> <span class="font-weight-bold  badge quelqum-headers " >{{$oferta->vacantes}}</span></span> 
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

