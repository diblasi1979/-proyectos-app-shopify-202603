<?php
error_reporting(E_ALL & ~E_WARNING);
require '../func/funtions.php';
require '../db/cnx.php';

// Shopify envía el cuerpo en bruto
$data = json_decode(file_get_contents("php://input"), true);

// (Opcional) Guardarlo o procesarlo
file_put_contents('../logs/ordenes.log', $data . PHP_EOL, FILE_APPEND);


$company = $data["line_items"][0]["vendor"];
$valor ="'%$company%'";
    $idorden = $data["id"];
    $indexid = $data["name"];
    $nroorder = $data["order_number"];
    $app_id = $data["app_id"];
    $financial_status = $data["financial_status"];
    $fechacre = $data["created_at"];

//var_dump($valor);


try {
    $insert_order = $pdo->prepare("INSERT INTO order_in (store_id, 
                                                         order_id, 
                                                         index_id, 
                                                         fechacre,
                                                         order_nro, 
                                                         financial_status)
                                        VALUES (:store,
                                                :idorden, 
                                                :indexid,
                                                :fechacre,     
                                                :nroorder,
                                                :financial_status);");
    $insert_order->bindParam(':store',$company);
    $insert_order->bindParam(':idorden', $idorden);
    $insert_order->bindParam(':indexid', $indexid);
    $insert_order->bindParam(':fechacre', $fechacre);
    $insert_order->bindParam(':nroorder', $nroorder);
    $insert_order->bindParam(':financial_status',$financial_status );
    $insert_order->execute();
    
    }
    catch (PDOException $e) {
                        echo "Error: " . $e->getMessage();
						file_put_contents($vd_psd, $e->getMessage() ."\n",  FILE_APPEND | LOCK_EX);
                        exit;
          }
    
    //var_dump(json_encode($data,true) ); exit;
	$datatest = $data["default_address"];

    //var_dump($datatest);

    //var_dump(json_encode($data,true) ); exit;
    //$data = json_encode($data,true) ;
    // Obtengo los datos del destinatario


    $first_name =  $data["customer"]["default_address"]["first_name"]. " " .$data["default_address"]["last_name"];
    $address1      =  $data["customer"]["default_address"]["address1"];
    $address2      =  $data["customer"]["default_address"]["address2"];
    $phone         =  $data["customer"]["default_address"]["phone"];
    $city          =  substr($data["customer"]["default_address"]["city"],0,38);
    $zip           =  $data["customer"]["default_address"]["zip"];
    $province      =  $data["customer"]["default_address"]["province"];
    $country       =  $data["customer"]["default_address"]["country"];
    $last_name     =  $data["customer"]["default_address"]["last_name"];
    $name          =  $data["customer"]["default_address"]["name"];
    $province_code =  $data["customer"]["default_address"]["province_code"];
    $note          =  'NOTAS: ' .$data["customer"]["note"];

     //var_dump($province);

     $calle = $address1 ." " .$address2;

     preg_match('/\d+/', $calle, $coincidencias);
     $nro = $coincidencias[0];
    
     //var_dump($nro);

     $nombreCalle = trim(str_replace($nro, '', $calle));

     //var_dump($nombreCalle);

     $address=array( "street_name"=>$nombreCalle,
                    "street_number"=>$nro,
                    "zip_code"=>$zip,
                    "city"=>$city,
                    "state"=>$province,
                    "between_1"=>1,
                    "between_2"=>2,
                    "other_info"=>'Calle y Nro: '.$address1.'  ' .$address2 .' '.$ResulSelectData[0]["note"],
                    "neighborhood_name"=> $province." / " .$city
                );

  $data_produtos= $data["line_items"];

  //var_dump(json_encode($produtos, true));  exit;
   $i =0;
  foreach ($data_produtos as $key1 => $produtos) {

        $line_items_id                           =  $produtos["id"];
        $line_items_quantity                     =  $produtos["quantity"];
        $line_items_price                        =  $produtos["price"];
        $line_items_sku                          =  $produtos["sku"];
        $depth = 1;
        $height = 1;
        $width = $produtos["grams"] ;

        

        $items[$i]= array(
                "item"=>$line_items_id,
                "sku"=> $line_items_sku,
                "height"=>$height,
                "quantity"=>$line_items_quantity
              );
              $price+=$line_items_price;
          
              $i++;
        

        }

        //var_dump(json_encode($items, true));

        $shipments[]=array(
            "shipping_cost"=>$price,
            "height"=>($max_height?$max_height:1),
            "width"=>($max_width?$max_width:1),
            "items_value"=>($price?$price:1),
            "delivery_shift"=>1,
            "length"=>1,
            "weight"=>($max_weight?$max_weight:1),
            "items"=>$items
          );

          var_dump(json_encode($shipments, true));
        

          $receiver=array("first_name"=>$data["customer"]["default_address"]["first_name"],
                          "last_name"=>$data["customer"]["default_address"]["last_name"],
            "receiver_name"=> $data["customer"]["default_address"]["first_name"] ." " .$data["customer"]["default_address"]["last_name"],
                          "receiver_phone"=>$data["customer"]["default_address"]["phone"],
                          "email"=>"no@informa.com",
                          "document"=>"NO INFORMA",
                          "address"=>$address);

        var_dump(json_encode($receiver, true));
          // Armado Final de Json de creacion de Orden

          $orden["order_id"]=$idorden;
          $orden["delivery_shift"]=2;
          $orden["delivery_mode"]=1;
          $orden["shipments"]=$shipments;
          $orden["receiver"]=$receiver;

  $ORDjson = json_encode($orden);

  var_dump($ORDjson);
  
  $consulta = $pdo->prepare("SELECT `usr-wms`, `psw-wms` FROM `sf_stores` WHERE `usr-store` like $valor ");
    $consulta->execute();
    $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

    $UsuarioAPI = $resultados[0]["usr-wms"] ;
    $ClaveAPI = $resultados[0]["psw-wms"];

    //Con los datos de API creo el token 
    $token = tokenapi($UsuarioAPI,$ClaveAPI);
        
    //var_dump($token);

    $CrearOrden = OrderCreateapi($token,$ORDjson);

    var_dump(json_encode($CrearOrden));








// Respondé con 200 para que Shopify sepa que lo recibiste bien
http_response_code(200);