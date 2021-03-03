<?php

namespace App\Http\Controllers;

use App\Product;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;

use GuzzleHttp\Client;
use App\Research;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{   
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pesquisar');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($input, $productArray)
    {
        $rsc = new Research();
        $product = new Product();
        $rsc->research = $input;
        $rsc->save();
        $src = Research::select('id')->where('research',$input)->first();
            
        foreach($productArray as $var){
            $product = new Product();
            $product->description = $var->description;
            $product->path_photo = $var->path_photo;
            $product->price = $var->price;
            $product->site = $var->site;
            $product->category = $var->category;
            $product->research_id = $src->id;

            $product->save();
            unset($product);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }


    public function findUrlPhotoML($html, $find){
        $dump = explode($find.'"}},"grid"',$html,2);
        $indice_dump = strripos($dump[0],'"retina":"');
        $partUrl = substr($dump[0],$indice_dump+10);
        $url = preg_split('/\",\"/',$partUrl);
        return str_replace('\u002F','/',$url[0]);
    }

    
    public function checkBd($input, $slcCategory, $slcSite){
        $rsc = Research::select('id')->where('research',$input)->first();
        if($rsc!=null){
          if($slcSite != 'Outros' && $slcCategory != 'Outros'){
                $arrayProduct = DB::select("select path_photo,description,category,price,site,research_id
                from products where research_id = :rsc and site = :slcsite and category = :slccategory", ['rsc'=>$rsc->id,'slcsite'=>$slcSite,'slccategory'=>$slcCategory]);
            }else if($slcSite != 'Outros'){
                $arrayProduct = DB::select("select path_photo,description,category,price,site,research_id
                from products where research_id = :rsc and site = :slcsite", ['rsc'=>$rsc->id,'slcsite'=>$slcSite]);
            }else if ($slcCategory != 'Outros'){
                $arrayProduct = DB::select("select path_photo,description,category,price,site,research_id
                from products where research_id = :rsc and category = :slccategory", ['rsc'=>$rsc->id,'slccategory'=>$slcCategory]);
            }
            // var_dump($rsc->id,$slcSite,$slcCategory);
        }else{
             $arrayProduct = null;
        }
        return $arrayProduct;
    }


    public function find(Request $request)
    {   
        $input= '';
        $input = $request->input;
        $slcCategory = $request->slc_category;
        $slcSite = $request->slc_site;
        $webSite = 0;
        if($input==null && $slcCategory=='Outros' && $slcSite=='Outros'){ return redirect()->route('index');}
        switch($slcCategory){
            case 'Celular':
                 $input != null ? $input = 'Celular '.$input : $input = 'Celular';
                break;
            case 'Geladeira': 
                $input != null ? $input = 'Geladeira '.$input : $input ='Geladeira';
                break;
            case 'Tv': 
                $input != null ? $input = 'Tv '.$input : $input = 'tv';
                break;
        }  
        
        switch($slcSite){

            case 'Outros': 
                $random =rand(1,2);
                if($random==1){
                    $link = 'https://lista.mercadolivre.com.br/'.rawurlencode(basename($input)).'_DisplayType_LF';
                }else{
                    $link = 'https://www.buscape.com.br/search?q='.rawurlencode(basename($input));
                }
                $webSite = 1;
                break;
            case 'Mercado livre':
                $link = 'https://lista.mercadolivre.com.br/'.rawurlencode(basename($input)).'_DisplayType_LF';
                $webSite = 1;
                break;
            case 'Buscape': 
                $link = 'https://www.buscape.com.br/search?q='.rawurlencode(basename($input));
                $webSite = 2;
                break;
        }

        $productArray=Array();
        if($input == ''){
            return redirect()->route('index');
        }else{$productArray = $this->checkBd($input, $slcCategory,$slcSite);}

        if($productArray==null){
            $client= new Client();
            $response=$client->request('GET',$link);
            $htmlResponse = $response->getBody();
            
            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $doc->loadHTML($htmlResponse);
        
            $xpath = new DOMXPath($doc);
            
            unset($productArray);
            $productArray=Array();

            if($webSite == 1){ //mercado livre
                $description = $xpath->query('//section/ol/li/div/div/div/div/a/h2');
                $priceReal = $xpath->query('//section/ol/li/div/div/div/div/div/div/div/div/span/span[2]');
                $priceCentavo = $xpath->query('//section/ol/li/div/div/div/div/div/div/div/div/span/span[4]');
               
                $textHtml1 = (string)str_replace('\u002F','/',$htmlResponse);
                $textHtml = str_replace('\"','"',$textHtml1);
                $site='Mercado livre';

                for($i=0;$i<count($description);$i++){
                    $product = new Product();
                    $product->description = $description[$i]->textContent;
                    $product->path_photo = $this->findUrlPhotoML($textHtml,$description[$i]->textContent);
                    if($priceCentavo[$i]!=null){
                        $price = $priceReal[$i]->textContent.",".$priceCentavo[$i]->textContent;
                    }else{
                        $price = $priceReal[$i]->textContent;
                    }
                    $product->price = 'R$ '.$price;
                    $product->site = $site;
                    $product->category = $slcCategory;
                    $productArray[] = $product;
                    unset($product);
                }

                if($slcCategory != 'Outros' || $slcSite != 'Outros'){
                    $this->store($input, $productArray);
                }
            

            }else if($webSite == 2){ //buscape

                // $priceReal = $xpath->query('//span[@class="customValue"][1]/span[@class="mainValue"][1]');
                // $priceCentavo = $xpath->query('//span[@class="customValue"][1]/span[@class="centsValue"][1]'); 
                // $description = $xpath->query('//*[@id="resultArea"]/div[3]/div/div/div[2]/a[@class="name"]');
                // $path_photo = $xpath->query('//a[@class="cardImage"][1]/img[1]/@src');


                $priceReal = $xpath->query('//*[@id="resultArea"]/div[3]/div/div/div/div/div/div/a[@class="price"][1]/span/span[1]');
                $priceCentavo = $xpath->query('//*[@id="resultArea"]/div[3]/div/div/div/div/div/div/a[@class="price"][1]/span/span[2]'); 
                $description = $xpath->query('//*[@id="resultArea"]/div[3]/div/div/div[2]/a[@class="name"]');
                $path_photo = $xpath->query('//*[@id="resultArea"]/div[3]/div/div/a[@class="cardImage"]/img/@src');


                $textHtml = (string)str_replace('\"','"',$htmlResponse);
                $site = "Buscape";

                for($i=0;$i<count($description);$i++){
                    $product = new Product();
                    $price = $priceReal[$i]->textContent.$priceCentavo[$i]->textContent;
                    $product->description = $description[$i]->textContent;
                    $product->price = $price;
                    $product->path_photo =$path_photo[$i]->textContent;
                    $product->site = $site;
                    $product->category = $slcCategory;
                    $productArray[] = $product;
                    unset($product);
                }
                
                if($slcCategory != 'Outros' || $slcSite != 'Outros'){
                    $this->store($input, $productArray);
                }
            }
        }

            return view('pesquisar',['products'=>$productArray]);
    }  
 
}
