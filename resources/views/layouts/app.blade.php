<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <!-- <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet"> -->

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/misEstilos.css') }}" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>


</head>
<body>
   <header>
        <div class="container-fluid ">
            <div class="row quelqum-headers">
                <div class="col-2">
                   <a class="navbar-brand pl-4"href="http://quelqum.com/"><img src={{url('images/quelqum.png')}}></a>
                </div>
                <div class="col-10 ">
                    <div class="row">
                        <nav class="navbar navbar-expand-lg my-auto mx-auto">
                            
                            <div class="collapse navbar-collapse" id="navbarNav">
                              <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link quelqum-text small" href="http://quelqum.com/">INICIO </a>
                                </li> 
                                <li class="nav-item px-4">
                                  <a class="nav-link quelqum-text small " href="http://quelqum.com/catalogo-de-cursos-2/">CERTIFICADOS DE PROFESIONALIDAD</a>
                                </li>
                                <li class="nav-item px-4">
                                  <a class="nav-link quelqum-text small" href="http://quelqum.com/category/cursos/">CURSOS</a>
                                </li>
                                <li class="nav-item">
                                  <a class="nav-link quelqum-text small " href="http://quelqum.com/agencia-de-colocacion/">AGENCIA DE COLOCACIÓN</a>
                                </li>
                              </ul>
                            </div>
                        </nav> 

                    </div>
                    
                    <div class="row">
                        <h1 class="text-white space-letter mx-auto mb-3">Ofertas de empleo en Galicia</h1>
                    </div>
                    
                </div>
            </div> 
            
                 <!-- Seleccion -->
       <div class="row pb-3 quelqum-headers ">
        <div class="col-md-2">
            
        </div>
        <div class="col-md-1">
            <div class="loader-container" id='loader-container'>
                <div class="loader"></div>
                <div class="loader2"></div>
           </div>
        </div>

        


        <div class="col-md-2">
            <div class="card border-0 quelqum-headers">
                <label class="pl-2 quelqum-text quelqum-headers text-center ">Provincia</label>
               
                
                <select class="form-control form-control-sm" id="provincia" name = "provincia">
                    
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
        
        


        
        <div class="col-md-2">    
            <div class="card quelqum-headers border-0 ">
                <label class="pl-2  quelqum-text text-center" >Localidad</label>
                
                <select id="localidad" name = "localidad" class="form-control form-control-sm ">
                    
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

        <div class="col-md-2">    
            <div class="card quelqum-headers border-0 ">
                <label class="pl-2 quelqum-text quelqum-headers text-center ">Fuente</label>
                <select id="fuente" name = "fuente" class="form-control form-control-sm ">
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
        <div class="col-md-3">
          
        </div>
        
    </div>  

       <!-- End Selección -->  
            
        </div>
    </header>
    <article>
        @yield('content')
    </article>

    <footer class="footer">
      <div class="container-fluid quelqum-headers">
       <div class="row ">
            <div class="col-5 my-auto mx-auto">
                <nav class="navbar navbar-expand-lg ">
                            
                    <div class="collapse navbar-collapse" id="navbarNav">
                      <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link quelqum-text small" href="http://quelqum.com/informacion-legal/">información legal</a>
                        </li> 
                        <li class="nav-item px-4">
                            <a class="nav-link quelqum-text small"  href="http://quelqum.com/politica_calidad">política de calidad</a>
                        </li>
                        <li class="nav-item px-4">
                            <a class="nav-link quelqum-text small"  href="http://quelqum.com/instalaciones/">instalaciones</a>
                        </li>
                      </ul>
                    </div>
                </nav> 
            </div>
            <div class="col-2 d-flex justify-content-center">
                <a class="navbar-brand" href="http://quelqum.com/"><img src={{url('images/quelqum.png')}}></a>
            </div>
            <div class="col-5 my-auto mx-auto ">
                <nav class="navbar navbar-expand-lg">
                            
                    <div>
                      <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link quelqum-text small" href="http://quelqum.com/">INICIO </a>
                        </li> 
                        
                        <li class="nav-item">
                          <a class="nav-link quelqum-text small" href="http://quelqum.com/category/cursos/">CURSOS</a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link quelqum-text small " href="http://quelqum.com/agencia-de-colocacion/">AGENCIA DE COLOCACIÓN</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link quelqum-text small " href="http://quelqum.com/catalogo-de-cursos-2/">CERTIFICADOS DE PROFESIONALIDAD</a>
                          </li>
                      </ul>
                    </div>
                </nav> 

            </div>
       </div>

      </div>
    </footer>

    @yield('script')
</body>
</html>
