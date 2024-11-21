<?php 

require "connection"; 
require "vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key; 

header ('Content=Type: application/json');


function sendJsonResponse($data, $statusCode =200){
    http_response_code($statusCode);
    echo json_encode($data);
    exit(); 
}

$secretKey = "zLqxD6TqRd5NV57jd8dVQj2jFK7fphgOWO/4mnCisjYX4RhWQDzxOqR4CXN0rh72IbpWoTSes3Cd6qABhT5ZSw==";

$headers=getallheaders();
