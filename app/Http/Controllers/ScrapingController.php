<?php

namespace App\Http\Controllers;

use Exception;
use App\Empleo;
use App\Fuente;
use App\Localidad;
use App\Provincia;
use Goutte\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;
use Symfony\Component\DomCrawler\Crawler;
use League\CommonMark\Delimiter\Delimiter;
use Symfony\Component\CssSelector\Node\SelectorNode;

class ScrapingController extends Controller
{
   // DATOS POR OFERTA
   
    /* 
    $oferta['fecha'] 
    $oferta['provincia]
    $oferta['localidad']
    $oferta['titulo']
    $oferta[vacantes]
    $oferta['detalles']
    $oferta['url'] 
    $oferta['fuente']
    */
    
    
    
    public function index() {
        
        $totalOfertasEmpleo = [];
        
       
        
        try {
            $totalOfertasXunta = $this->xunta();
            $totalOfertasEmpleo = array_merge($totalOfertasEmpleo, $totalOfertasXunta);
        } catch (Exception $e) {
            echo 'Excepción capturada en scraping Xunta: ',  $e->getMessage(), "\n";
        } 
       
        try {
            $totalOfertasCersia = $this->cersia();
            $totalOfertasEmpleo = array_merge($totalOfertasEmpleo, $totalOfertasCersia);
        } catch (Exception $e) {
            echo 'Excepción capturada en scraping Cersia: ',  $e->getMessage(), "\n";
        }
        
        try {
            $totalOfertasAytoCoru = $this->ayuntamientoCor();
            $totalOfertasEmpleo = array_merge($totalOfertasEmpleo, $totalOfertasAytoCoru);
        } catch (Exception $e) {
            echo 'Excepción capturada en scraping Ayuntamiento Coruña: ',  $e->getMessage(), "\n";
        }  
        
        try {
            $totalOfertasAytoLug = $this->ayuntamientoLugo();
            $totalOfertasEmpleo = array_merge($totalOfertasEmpleo, $totalOfertasAytoLug);
        } catch (Exception $e) {
            echo 'Excepción capturada en scraping Ayuntamiento Lugo: ',  $e->getMessage(), "\n";
        }  

    
        try {
            $this->storeDataBase($totalOfertasEmpleo);
        } catch (Exception $e) {
            echo 'Excepción capturada en generar BD: ',  $e->getMessage(), "\n";
        }

       /*  $totalOfertasAytoCoru = $this->ayuntamientoCor();
        print_r($totalOfertasEmpleo); */
       
        
       
    }


    public function storeDataBase ($ofertas) {
       
        
        DB::transaction(function () use($ofertas){
            Schema::disableForeignKeyConstraints();
            
            (new Empleo())->newQueryWithoutScopes()->forceDelete();
            (new Provincia())->newQueryWithoutScopes()->forceDelete();
            (new Localidad())->newQueryWithoutScopes()->forceDelete();
            (new Fuente())->newQueryWithoutScopes()->forceDelete();

            
            foreach ($ofertas as $eachoferta) {
                
                
                $empleo = new Empleo;
                $empleo['titulo'] = $eachoferta['titulo'];
                $empleo['provincia'] = $eachoferta['provincia'];
                $empleo['localidad'] = $eachoferta['localidad'];
                

                $empleo['fecha'] = $eachoferta['fecha'];
                $empleo['vacantes'] = $eachoferta['vacantes'];
                $empleo['detalles'] = $eachoferta['detalles'];
                $empleo['url'] = $eachoferta['url'];
                $empleo['fuente'] = $eachoferta['fuente'];
                
                
                $empleo->save();

                

                $localidad = Localidad::where('name','=',$eachoferta['localidad'])->first();
                
                if (!$localidad) {
                    $localidad = new Localidad;
                    $localidad->name = $eachoferta['localidad'];
                    $localidad->save();
                }
                
                $provincia = Provincia::where('name','=',$eachoferta['provincia'])->first();
                if (!$provincia) {
                    $provincia = new Provincia;
                    $provincia->name = $eachoferta['provincia'];
                    $provincia->save();
                }

                $fuente = Fuente::where('name','=',$eachoferta['fuente'])->first();
                if (!$fuente) {
                    $fuente = new Fuente();
                    $fuente->name = $eachoferta['fuente'];
                    $fuente->save();
                }



                
                $provincia->empleos()->save($empleo) ;
                $provincia->localidades()->save($localidad);
                $localidad->empleos()->save($empleo);
                $fuente->empleos()->save($empleo);

            }
            Schema::enableForeignKeyConstraints();
        });
       
    }

    public function xunta(){
        
        $urlsProvincias = [
            'Coruña' => 'https://emprego.xunta.gal/portal/index.php/es/buscar-empleo.html?&idProvincia=CORUNA&emprego=',
            'Lugo' => 'https://emprego.xunta.gal/portal/index.php/es/buscar-empleo.html?&idProvincia=LUGO&emprego=',
            'Orense' => 'https://emprego.xunta.gal/portal/index.php/es/buscar-empleo.html?&idProvincia=OURENSE&emprego=',
            'Pontevedra' => 'https://emprego.xunta.gal/portal/index.php/es/buscar-empleo.html?&idProvincia=PONTEVEDRA&emprego='
        
        ];
        $ofertahtlm = new Crawler();
        $totalOfertasXunta = [];
        foreach($urlsProvincias as $key => $val) {
            //print "$key = $val <br>";
           
            $client = new Client();
            $crawlerProvincia = $client->request('GET', $val);  
            $GLOBALS['provincia'] = $key;
            $GLOBALS['url'] = $val;
            
            $ofertasProvincia = $crawlerProvincia->filter('table.expandable_table')->each(function (Crawler  $htlmRead) {
                $oferta = [];
                $GLOBALS['inicial'] = $htlmRead;
               
                $ofertasXunta = $htlmRead->children()->filter('tr.master')->each(function (Crawler $trmaster){
                    $idOferta = 'cual'.($trmaster->extract(['id']))[0] ;
                   
                    $td = $trmaster->children()->filter('td');
                    $oferta['titulo']= $td->eq(1)->text();
                    $oferta['localidad'] = $this->trataLocalidad($td->eq(2)->text());
                    $fecha = $td->eq(3)->text();
                    //var_dump($fecha);
                    //$oferta['fecha'] = now();
                    $fecha = strtotime(str_replace('/', '-', $fecha));
                    $oferta['fecha'] = date('Y-m-d', $fecha);


                    $detalle = $GLOBALS['inicial']->filter("#$idOferta");
                    $pulsDatos = $detalle->children()->filter('dd');
                    $partePrueba = $pulsDatos->eq(1)->text();
                    $parte1 = $pulsDatos->eq(1)->text();
                    //$p =  mb_detect_encoding($parte1);
                
                    $parte1 = ucfirst(mb_strtolower($parte1,"UTF-8"));
                    $parte2 = $pulsDatos->eq(4)->text();
                    $oferta['detalles'] = $parte1.'<br>'.$parte2;
                    
                    $oferta['provincia'] = $GLOBALS['provincia'];
                    $oferta['url'] = $GLOBALS['url'];
                    $oferta['vacantes'] = 1;
                    $oferta['fuente'] = 'Xunta de Galicia';
                    return $oferta;
                    //array_push($GLOBALS['totalOfertasEmpleo'],$oferta);
                });
               return $ofertasXunta; 
            });
           $totalOfertasXunta = array_merge($totalOfertasXunta,$ofertasProvincia[0]);
        }
        return $totalOfertasXunta;
       
    }
        

    
    public function cersia() {
     
        $urlsProvincias = [
            'Coruña' => 'https://axencialocaldecolocacion.org/ofertas?provincias=15&pageSize=300',
            'Lugo' => 'https://axencialocaldecolocacion.org/ofertas?provincias=27&pageSize=300',
            'Orense' => 'https://axencialocaldecolocacion.org/ofertas?provincias=32&pageSize=300',
            'Pontevedra' => 'https://axencialocaldecolocacion.org/ofertas?provincias=36&pageSize=300'
        
        ];
        $todasUrls = [];
        
        foreach($urlsProvincias as $key => $val) {
            global $urlsProvincias;
     
            $client = new Client();
            $crawler = $client->request('GET', $val); 
            $t1 = $crawler->filter('ul.ofertas > h3');
        
           
            if ($t1->count()>0 && $t1->eq(0)->text() == 'Non se atoparon resultados. Pregase que o intente de novo usando outro filtro') {
                continue;
            }
           
            $GLOBALS['key'] = $key; 
            
           
            
            
            $urlsCresiaOfeertas =  $crawler->filter('li.item')->each(function (Crawler $ofertahtlm) {
                $urls = [];
                $t1 = $ofertahtlm->filter('a.oferta-ficha');
                $urls['url'] = $t1->extract(['href'])[0];
                $urls['provincia'] =$GLOBALS['key'];
                $t1 = $ofertahtlm->filter('div .text-conte > p');
                $urls['localidad'] = $t1->eq(0)->text();
                $t1 = $ofertahtlm->filter('p.date >i');
                $urls['fecha'] = ($t1->text());
                $t1 = $ofertahtlm->filter('div.text-conte > h2');
                $urls['titulo'] = $t1->text();
                return $urls;
            });
            
            
            $todasUrls = array_merge($todasUrls,$urlsCresiaOfeertas);
            
        }
       
        $totalOfertasCersia = [];
        
        foreach ($todasUrls as $url) {
            global $vacantes;
            $oferta = [];
            $client = new Client();
            $ofertasCersia = $client->request('GET',$url['url']);
            $t1 = $ofertasCersia->filter('section.contenido');
            
            // vacantes
            $t2 = $t1->filter('div.otras-c');
            $tt = $t2->html();
            $porciones = explode("<br>", $tt);
            
         
            $vacantes = '1';
            foreach($porciones as $parte) {  
                
                if (strpos($parte, 'vacantes') !== false) { 
                    //dd(str_replace('Número de vacantes:','',$parte));
                    $vacantes = filter_var($parte, FILTER_SANITIZE_NUMBER_INT);
                }
            }
            
            $oferta['vacantes'] = $vacantes;
            $oferta['fuente'] = 'Ayuntamiento de Santiago';
            

            $fecha = $url['fecha'];
            $fecha =str_replace(' de ', '-', $fecha);
            $fecha = $this->convierteFecha($fecha);       
            $fecha = strtotime($fecha);
            $oferta['fecha'] = date('Y-m-d', $fecha);

            $detalle = $t1->children()->filter('div.description-o')->eq(0)->text();
                        $detalle= $this->trataDetallesCersia($detalle);

                        
             $oferta['detalles'] = $detalle;
                       



            $oferta['provincia'] = $url['provincia'];
            $oferta['localidad'] = $this->trataLocalidad($url['localidad']); 
            $oferta['titulo'] = $url['titulo'];
            $oferta['url'] = $url['url'];
            array_push($totalOfertasCersia, $oferta);
        }

        return $totalOfertasCersia;
    }

    public function ayuntamientoCor() {
        
        $totalUrls = [];
        $client = new Client();
        for ($i=1; $i < 20; $i++ ) {
            
            $val = 'https://www.coruna.gal//sites/Satellite?c=Page&amp;cid=1453667420099&amp;pagename=Empleo20%2FPage%2FGenerico-Page-Generica&amp;argPag='.$i.'#Componente1354759957937%22,%221354759957937%22,%22Empleo20';
            $crawler = $client->request('GET', $val); 
            /* $existeOferta= $crawler->filter('header >h1 >a'); */
           
            $existeOferta= $crawler->filter('li.listadoOfertaTrabajo');
            
            if ($existeOferta->count() == 0) {
                
                break;
            }
            
            $urlsOfertas =  $existeOferta->each(function (Crawler $ofertahtlm) {
                $urls = [];
                
                
                $t1 = $ofertahtlm->children()->filter('p.fecha'); 
                if ($t1->count() == 0)  {
                    return;
                }
                $urls['fecha'] = $t1->text(); 
                
                $t1 = $ofertahtlm->children()->filter('p.lugar_solicitud'); 
                if ($t1->count() == 0)  {
                    $urls['localidad'] ='CORUÑA, A';
                } else {
                    $urls['localidad'] = $this->trataLocalidad($t1->text()); 
                }
                
                $t1 = $ofertahtlm->children()->filter('a');
                $urls['titulo'] = $t1->text();
               
                
                $urls['url'] = $t1->extract(['href'])[0];
                //echo basename($urls['url'].'<br>');
                return $urls;
            });
            
           
            $totalUrls = array_merge($totalUrls,$urlsOfertas); 
        }
        
        $result = array_filter($totalUrls);   
       
        $totalOfertasAytoCor= [];
       
        
        foreach ($totalUrls as $urlOferta) {
            if ($urlOferta['url']=='') {
                break;
            }
            $oferta = [];
            $numeroOferta = basename($urlOferta['url']);
            $url = 'https://www.coruna.gal'.$urlOferta['url'];
            
            $client = new Client();
            $crawler = $client->request('GET',$url); 
            
            $ofertaAytoCoru = $crawler->filter('div.detalleOfertaTrabajo');
            $oferta['provincia'] = "Coruña";
           
            $oferta['titulo'] = $urlOferta['titulo'];
            
           
            $fecha = $urlOferta['fecha'];
           
            $fecha =str_replace(' de ', '-', $fecha);
            $fecha = $this->convierteFecha($fecha);       
            
            $fecha = strtotime($fecha);
            $oferta['fecha'] = date('Y-m-d', $fecha);
            
            $oferta['localidad'] = $urlOferta['localidad'];

            $t1 =  $ofertaAytoCoru->children()->filter('p.numero_plazas');
            if ($t1->count()>0) {
                $oferta['vacantes'] = $t1->text();
            } else {
                $oferta['vacantes'] = '1';
            }
            
            
           $oferta['detalles'] = $this->trataDetallesAytoCor($ofertaAytoCoru);
           
            $oferta['url'] = $url;
            $oferta['fuente'] = "Ayuntamiento de la Coruña";
            //$totalOfertasAytoCor = array_merge($totalOfertasAytoCor,$oferta); 
            array_push($totalOfertasAytoCor,$oferta);
        }
        
        return $totalOfertasAytoCor;
        
        
       
    }

    public function ayuntamientoLugo() {
    
        $totalUrls = [];
        $client = new Client();
        
        
            
        $val = 'https://lugo.portalemp.com/ofertas.html';
        $crawler = $client->request('GET', $val); 
        /* $existeOferta= $crawler->filter('header >h1 >a'); */
        $existeOferta = $crawler->filter('table.tabla_responsive');
        
        $tr = $existeOferta->children()->filter('tr');
        
        
        if ($tr->count() == 0) {
            return;
        }
        
        $totalUrls = [];

        
        for($i=1; $i < $tr->count() ;$i++) {
            $td = $tr->eq($i)->filter('td');
            $url['fecha'] = ($td->eq(0)->text());      
            
            $fecha =str_replace('/', '-', $url['fecha']);
            $fecha = strtotime($fecha);        
            $url['fecha'] = date('Y-m-d', $fecha);
            
            $tda =$tr->eq($i)->filter('td > a'); 
            $url['url'] = $tda->eq(0)->extract(['href'])[0];
            array_push($totalUrls,$url);
        }
           
            
        
        $totalOfertasAytoLugo = [];
        foreach ($totalUrls as $urlOferta) {
            if ($urlOferta['url']=='') {
                break;
            }
            $oferta = [];

            $url = 'https://lugo.portalemp.com/'.$urlOferta['url'];
            
            $oferta['url'] = $url;
            $oferta['fecha'] = $urlOferta['fecha'];
            $oferta['provincia'] = 'Lugo';
            $client = new Client();
            $crawler = $client->request('GET',$url); 
            
            $ofertaAytoLugo = $crawler->filter('div.ficha');
            $x = $ofertaAytoLugo->filter('p');
            $oferta['titulo'] = str_replace('Nombre de la oferta ', '', $x->eq(1)->text());
            $trozos = explode(", ", $x->eq(8)->text());
             $pp = str_replace('Municipio: ', '', $trozos[1]);
             $oferta['localidad'] = $this->trataLocalidad($pp);
            
            $oferta['vacantes'] = $x->eq(6)->children()->eq(1)->text();
            $detalleParte1 = "";
            $detalleParte2 = "";
            $x = $ofertaAytoLugo->filter('p > label');
            //dd($x->eq(13)->text());
            //dd($x->count());
            for($i=0; $i < ($x->count() - 1) ; $i++){
                
                if ($x->eq($i)->text() == "Funciones") {
                    $t = $x->eq($i)->siblings();
                    $detalleParte1 = $t->text();
                }
                if ($x->eq($i)->text() == "Observaciones jornada") {
                    $t = $x->eq($i)->siblings();
                    $detalleParte2 = '<br>'.$t->text();
                }
            }
            
            $oferta['detalles'] = $detalleParte1.$detalleParte2; 
            $oferta['fuente'] = 'Ayuntamiento de Lugo';
            array_push($totalOfertasAytoLugo,$oferta);
        
        }
       
        return  $totalOfertasAytoLugo;
    }




    public function trataDetallesAytoCor($crawler) {
        
        $t1 = $crawler->children()->filter('section.estudios');
        if($t1->count()>0) {
            $estudios = $t1->text();
            $estudios = str_replace('Estudos:','<strong class="text-muted" >Estudos:</strong>',$estudios);
            
        } else {
            $estudios = "";
        }
       
        $t1 = $crawler->children()->filter('section.experiencia');
        if($t1->count()>0) {
            $experiencia = $t1->text();
            $experiencia = str_replace('Experiencia laboral: ','<strong class="text-muted" >Experiencia laboral: </strong>',$experiencia);
        
        } else {
            $experiencia = "";
        } 
        
        $t1 = $crawler->children()->filter('section.requisitos');
        if($t1->count()>0) {
            $requisitos = $t1->text();
            $requisitos = str_replace('Requisitos: ','<strong class="text-muted" >Requisitos: </strong>',$requisitos);
        } else {
            $requisitos = "";
        } 
       
        $t1 = $crawler->children()->filter('section.descripcion_larga');
        if($t1->count()>0) {
            $descripcion = $t1->text();
            $descripcion = '<strong class="text-muted" >Funciones: </strong>'.$descripcion;
        } else {
            $descripcion = "";
        } 
    
        return $estudios.'<br>'.$experiencia.'<br>'.$requisitos.'<br>'.$descripcion;
    }

    public function trataLocalidad($localidad) {
        
        if ($localidad == 'A Coruña') {
            $localidad = 'CORUÑA, A';
           
            return strtoupper($localidad);
        }
                         
        if ($localidad == 'Iñás (Oleiros)') {
            $localidad = 'OLEIROS';
            return strtoupper($localidad);
        }
        if ($localidad == 'Narón, A Coruña') {
            $localidad = 'NARON';
            return strtoupper($localidad);
        }
        if ($localidad == 'Perillo (Oleiros)') {
            $localidad = 'OLEIROS';
            return strtoupper($localidad);
        }
        if ($localidad == 'Provincia: Lugo') {
            $localidad = 'LUGO';
            return strtoupper($localidad);
        }

        if(strpos($localidad, 'Teletrabajo') == true) {
            $localidad = "CORUÑA,A";
            return strtoupper($localidad);
        }
        return strtoupper($localidad);
    }

   
    public function trataDetallesCersia($detalles) {
        $detallesTemp = str_replace('REQUISITOS:','',$detalles);
        $detallesTemp = str_replace('Formación:','<strong class="text-muted" >Formación:</strong>',$detallesTemp);
        $detallesTemp = str_replace('Experiencia:','<br><strong class="text-muted">Experiencia:</strong>',$detallesTemp);
        $detallesTemp = str_replace('Outros:','<br><strong class="text-muted">Outros:</strong>',$detallesTemp);
        $detallesTemp = str_replace('DESCRICIÓN:','<br><strong class="text-muted">Funciones:</strong>',$detallesTemp);
        return $detallesTemp;
    }
   
   
    public function convierteFecha($fecha) {
            
        $r = ['Xaneiro','xaneiro'];
        $fecha2 = str_replace($r, '01', $fecha);
        if($fecha2!=$fecha) {
            return $fecha2;
        }
         
        $r = ['Febreiro','febreiro'];
        $fecha2 = str_replace($r, '02', $fecha);
        if($fecha2!=$fecha) {
            return $fecha2;
        }
       
        $r = ['Marzal','marzal'];
        $fecha2 = str_replace($r, '03', $fecha);
        if($fecha2!=$fecha) {
            return $fecha2;
        }
        
        $r = ['Abril','abril'];
        $fecha2 = str_replace($r, '04', $fecha);
        if($fecha2!=$fecha) {
            return $fecha2;
        }
    
        $r = ['Maio','maio'];
        $fecha2 = str_replace($r, '05', $fecha);
        if($fecha2!=$fecha) {
            return $fecha2;
        }
       
        $r = ['Xuño','xuño'];
        $fecha2 = str_replace($r, '06', $fecha);
        if($fecha2!=$fecha) {
            return $fecha2;
        }
       
        $r = ['Xullo','xullo'];
        $fecha2 = str_replace($r, '07', $fecha);
        if($fecha2!=$fecha) {
            return $fecha2;
        }
        
        $r = ['Agosto','agosto'];
        $fecha2 = str_replace($r, '08', $fecha);
        if($fecha2!=$fecha) {
            return $fecha2;
        }
      
        $r = ['Setembro','setembro'];
        $fecha2 = str_replace($r, '09', $fecha);
        if ($fecha2!=$fecha) {
            return $fecha2;
        }
      

        $r = ['Outubro','outubro'];
        $fecha2 = str_replace($r, '10', $fecha);
        if($fecha2!=$fecha) {
            return $fecha2;
        }
        
        $r = ['Novembro','novembro'];
        $fecha2 = str_replace($r, '11', $fecha);
        if($fecha2!=$fecha) {
            return $fecha2;
        }
        $r = ['Decembro','decembro'];
        $fecha2 = str_replace($r, '12', $fecha);
        if($fecha2!=$fecha) {
            return $fecha2;
        }
    }

    



}
