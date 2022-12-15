<?php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  include_once('./controllers/api/client_api.php');

  //$arrData = getProduct();
  //$arrData = postProduct();

  function getProduct($id = ""){
    $URL          = "http://localhost:8080/products";
    if(!empty($id)){
      $URL        .= "/".$id;
    }
    $clsClientAPI = new clientAPI();
    $arrResult    = $clsClientAPI->getData($URL);

    return $arrResult;
  }

  function postProduct(){
    /*
    {
        "name": "Mesin 02",
        "stock": 50,
        "price": 10000
    }
    */

    $arrData  = array(
      "name"  => "Mesin Test",
      "stock" => 26,
      "price" => 5000,
    );

    $URL          = "http://localhost:8080/product";
    $clsClientAPI = new clientAPI();
    $arrResult    = $clsClientAPI->postData($URL, $arrData);

    return $arrResult;
  }
?>