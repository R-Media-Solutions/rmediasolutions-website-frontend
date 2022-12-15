<?php
  class clientAPI{
    private $companyCode  = "12345";
    private $hostUrl      = "";
    private $clientID     = "";
    private $clientSecret = "";
    private $APIKey       = "";
    private $APISecret    = "";
    private $accessToken  = null;
    private $timeStamp    = null;
    private $client;

    private $postdata     = array();
    private $arrResponse  = array(
      'success' => 1,
      'message' => '', 
      'data' => array()
    );
    
    public function __construct() {
      ini_set('display_errors', 0);
      
      if (isset($_SERVER['HTTP_ORIGIN'])) {
          //header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
          header('Access-Control-Allow-Credentials: true');
          //header('Access-Control-Max-Age: 86400');    // cache for 1 day
          header('Access-Control-Max-Age: 60');    // cache for 1 day
      }
   
      // Access-Control headers are received during OPTIONS requests
      if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {   
          if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
              header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
   
          if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
              header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
   
          exit(0);
      }
  
      $postdata = file_get_contents("php://input");
      $this->postdata = json_decode($postdata, true);
    }

    public function index() {
      echo "Client API";
    }

    public function getData($URL = ""){
      // cURL setting
      $ch   = curl_init();
      curl_setopt($ch, CURLOPT_URL, $URL);     // provide the URL to use
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);    // FALSE, blindly accept any server certificate, without doing any verification
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);    // FALSE, not verify the certificate's name against host
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // TRUE to return the transfer as a string of the return value of curl_exec()
      curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);    // TRUE to return the raw output when CURLOPT_RETURNTRANSFER is used
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);   // The number of seconds to wait while trying to connect
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);          // The maximum number of seconds to allow cURL functions to execute
  
      // cURL execute
      $result = curl_exec($ch);
      if(curl_error($ch)){
          die("CURL Error ::". curl_error($ch));
      }
      curl_close($ch);

      $obj = json_decode($result, TRUE);

      return $obj;
    }

    public function postData($URL = "", $arrData = array()) {
      // Prepare insert transaction data
      /*
      {
          "name": "Mesin 02",
          "stock": 50,
          "price": 10000
      }
      */
  
      if (!function_exists('curl_init')){   // Check existing cURL
          die('Sorry cURL is not installed!');
      }
  
      //$OPT        = http_build_query($arrData);
      $OPT        = json_encode($arrData);
  
      // cURL setting
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $URL);     // provide the URL to use
      curl_setopt($ch, CURLOPT_POSTFIELDS, $OPT);     // specify data to POST to server
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);    // FALSE, blindly accept any server certificate, without doing any verification
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);    // FALSE, not verify the certificate's name against host
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // TRUE to return the transfer as a string of the return value of curl_exec()
      curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);    // TRUE to return the raw output when CURLOPT_RETURNTRANSFER is used
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);   // The number of seconds to wait while trying to connect
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);          // The maximum number of seconds to allow cURL functions to execute
  
      // cURL execute
      $result = curl_exec($ch);
      if(curl_error($ch)){
          return $this->arrResponse = array(
            'success' => 0,
            'message' => "FAILED INSERT DATA ".curl_error($ch), 
            'data'    => array()
          );
      }
      curl_close($ch);
      $obj = json_decode($result, TRUE);

      return $this->arrResponse = array(
        'success' => 1,
        'message' => "SUCCESS INSERT DATA", 
        'data'    => $obj
      );
    }
    
    private function authorize() {
      return base64_encode($this->APIKey.":".$this->APISecret);
    }

    private function getHeaderList() {
      //create an array to put our header info into.
      $headerList = array();
      //loop through the $_SERVER superglobals array.
      foreach ($_SERVER as $name => $value) {
        //if the name starts with HTTP_, it's a request header.
        if (preg_match('/^HTTP_/',$name)) {
            //convert HTTP_HEADER_NAME to the typical "Header-Name" format.
            $name = strtr(substr($name,5), '_', ' ');
            $name = ucwords(strtolower($name));
            $name = strtr($name, ' ', '-');
            //Add the header to our array.
            $headerList[$name] = $value;
        }
      }
      //Return the array.
      return $headerList;
    }

    private function printResult($message='', $arrData=array()){
      //header('Content-Type: application/json');
      //header('Access-Control-Allow-Origin: *');

      $this->arrResponse['success'] = 1;
      $this->arrResponse['message'] = $message;
      $this->arrResponse['data'] = $arrData;

      echo json_encode($this->arrResponse);
      die();
    }

    private function printError($message=''){
      //header('Content-Type: application/json');
      //header('Access-Control-Allow-Origin: *');

      $this->arrResponse['success'] = 0;
      $this->arrResponse['message'] = $message;

      echo json_encode($this->arrResponse);
      die();
    }

    public function saveLogFile($type = "", $content = "empty") {
      $path = "log/api_log/".date('Y')."/".date('m');
      if (is_array($content)) $content = json_encode($content);
      $txt = date('H:i:s')." $type: $content";
      if (!file_exists($path))
        mkdir($path, 0777, true);
      $file = date('d').".txt";
      $myfile = file_put_contents($path."/$file", $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
      //echo "OK";
    }
    
    private function request($id, $default='') {
      if (empty($id)) {
        return $default;
      }
  
      if (isset($this->postdata[$id]))
        return $this->postdata[$id];
      return "";
    }

  }
?>