<?php

namespace App\Console\Commands;

use App\Empleo;
use App\Localidad;
use App\Provincia;
use Goutte\Client;
use Illuminate\Console\Command;
use PHPUnit\Framework\Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ScrapingEmpleo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraping:empleo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scraping empleos: Privados y Pùblicos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $totalOfertasEmpleo = [];
        
        try {
            $totalOfertasXunta = $this->xunta();
            $totalOfertasEmpleo = array_merge($totalOfertasEmpleo, $totalOfertasXunta);
        } catch (Exception $e) {
            Log::info('ERROR en scraping:empleo Xunta'.now());
        }

        try {
            $totalOfertasCersia = $this->cersia();
            $totalOfertasEmpleo = array_merge($totalOfertasEmpleo, $totalOfertasCersia);
        } catch (Exception $e) {
            Log::info('ERROR en scraping:empleo Cersia'.now().' '.$e->getMessage()); 
        }
    
        try {
            $this->storeDataBase($totalOfertasEmpleo);
        } catch (Exception $e) {
            Log::info('Excepción capturada en generar BD:'.now().'',  $e->getMessage());
        } 
   
        Log::info('Scrapin empleo ejecutado'.now());
   
    }
    public function storeDataBase ($ofertas) {
        DB::transaction(function () use($ofertas){
            (new Empleo())->newQueryWithoutScopes()->forceDelete();
            
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

                $provincia = Provincia::where('name','=',$eachoferta['provincia'])->first();
                if (!$provincia) {
                    $provincia = new Provincia;
                    $provincia->name = $eachoferta['provincia'];
                
                }
        
                $provincia->empleos()->save($empleo) ;
                $provincia->save();

                $localidad = Localidad::where('name','=',$eachoferta['localidad'])->first();
                if (!$localidad) {
                    $localidad = new Localidad;
                    $localidad->name = $eachoferta['localidad'];
                
                }
        
                $localidad->empleos()->save($empleo) ;
                $localidad->save();
            }
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
                    $oferta['localidad'] = $td->eq(2)->text();
                    $fecha = $td->eq(3)->text();
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
            $client = new Client();
            $crawler = $client->request('GET', $val); 
            $GLOBALS['key'] = $key; 

            $urlsCresiaOfeertas =  $crawler->filter('a.oferta-ficha')->each(function (Crawler $ofertahtlm) {
                $urls = [];
                $urls['url'] = ($ofertahtlm->extract(['href']))[0];
                $urls['provincia'] =$GLOBALS['key'];
                return $urls;
            });
            array_push($todasUrls,$urlsCresiaOfeertas);
        }

        $totalOfertasCersia = [];
       
        foreach($todasUrls as $eachProvincia) {
                $totalOfertasProvincia = [];
                foreach($eachProvincia as $key => $val) {
                    
                    $client = new Client();
                    $crawler = $client->request('GET',$val['url']); 
                    $GLOBALS['provincia'] = $val['provincia'];
                    $GLOBALS['url'] = $val['url'];
                    $ofertasProvincia = $crawler->filter('section.contenido')->each(function (Crawler $ofertahtlm) {
                        $oferta = [];
                        $oferta['titulo'] = $ofertahtlm->children()->filter('div.text-conte h1')->text();   
                        $oferta['localidad'] = $ofertahtlm->children()->filter('div.text-conte p')->text();  
                        $fechaRead = $ofertahtlm->children()->filter('span.date')->eq(0)->text();
                        
                        /* $fecha = (explode('DATA DE PUBLICACIÓN ',$fechaRead))[1];
                        $fecha = strtotime(str_replace('/', '-', $fecha));
                        $oferta['fecha'] = date('Y-m-d', $fecha); */

                        $fecha = (explode('DATA DE PUBLICACIÓN ',$fechaRead))[1];
                        $fecha =str_replace(' de ', '-', $fecha);
                        $fecha = $this->convierteFechaCersia($fecha);
                        $fecha = strtotime($fecha);
                        $oferta['fecha'] = date('Y-m-d', $fecha);


                        
                        $detalle = $ofertahtlm->children()->filter('div.description-o')->eq(0)->text();
                        $detalle= $this->trataDetallesCersia($detalle);
                        $oferta['detalles'] = $detalle;
                        
                        
                        
                        
                        
                        
                        
                        /* $detalle = str_replace('REQUISITOS: Experiencia: ','',$detalle);
                        $detalle = str_replace('DESCRICIÓN: ','. ',$detalle);
                        $otras = $ofertahtlm->children()->filter('div.filtro')->eq(0)->text();
                        $otras = str_replace('OUTRAS CARACTERÍSTICAS: Contrato:','',$otras);
                        $oferta['detalles'] = $detalle.' '.$otras;

 */

                        $oferta['fuente'] = 'Cersia';
                        $oferta['provincia']= $GLOBALS['provincia'];
                        $oferta['vacantes'] = '1';
                        $oferta['url'] = $GLOBALS['url'];
                        return $oferta;
                        //array_push($GLOBALS['totalOfertasEmpleo'],$oferta);            
                    }); 
                    $totalOfertasProvincia = array_merge($totalOfertasProvincia,$ofertasProvincia);
                }
                $totalOfertasCersia = array_merge($totalOfertasCersia,$totalOfertasProvincia);
                
            }
            return $totalOfertasCersia;
        }

        public function trataDetallesCersia($detalles) {
            $detallesTemp = str_replace('REQUISITOS:','',$detalles);
            $detallesTemp = str_replace('Formación:','<strong class="text-muted" >Formación:</strong>',$detallesTemp);
            $detallesTemp = str_replace('Experiencia:','<br><strong class="text-muted">Experiencia:</strong>',$detallesTemp);
            $detallesTemp = str_replace('Outros:','<br><strong class="text-muted">Outros:</strong>',$detallesTemp);
            $detallesTemp = str_replace('DESCRICIÓN:','<br><strong class="text-muted">Funciones:</strong>',$detallesTemp);
            return $detallesTemp;
        }

        public function convierteFechaCersia($fecha) {
            
            $fecha2 = str_replace('Xaneiro', '01', $fecha);
            if($fecha2!=$fecha) {
                return $fecha2;
            }
             
            
            $fecha2 = str_replace('Febreiro', '02', $fecha);
            if($fecha2!=$fecha) {
                return $fecha2;
            }
           
    
            $fecha2 = str_replace('Marzal', '03', $fecha);
            if($fecha2!=$fecha) {
                return $fecha2;
            }
            
            
            $fecha2 = str_replace('Abril', '04', $fecha);
            if($fecha2!=$fecha) {
                return $fecha2;
            }
        
    
            $fecha2 = str_replace('Maio', '05', $fecha);
            if($fecha2!=$fecha) {
                return $fecha2;
            }
           
            
            $fecha2 = str_replace('Xuño', '06', $fecha);
            if($fecha2!=$fecha) {
                return $fecha2;
            }
           
    
            $fecha2 = str_replace('Xullo', '07', $fecha);
            if($fecha2!=$fecha) {
                return $fecha2;
            }
            
           
            $fecha2 = str_replace('Agosto', '08', $fecha);
            if($fecha2!=$fecha) {
                return $fecha2;
            }
          
    
            $fecha2 = str_replace('Setembro', '09', $fecha);
            if ($fecha2!=$fecha) {
                return $fecha2;
            }
          
    
            
            $fecha2 = str_replace('Outubro', '10', $fecha);
            if($fecha2!=$fecha) {
                return $fecha2;
            }
            
    
            $fecha2 = str_replace('Novembro', '11', $fecha);
            if($fecha2!=$fecha) {
                return $fecha2;
            }
    
            $fecha2 = str_replace('Decembro', '12', $fecha);
            if($fecha2!=$fecha) {
                return $fecha2;
            }
        }

}
