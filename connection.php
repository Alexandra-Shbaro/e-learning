<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$host = "localhost:3307";
$dbuser = "root";
$dbpass = "alexandra";
$dbname = "e_learning";

$connection = mysqli_connect($host, $dbuser, $dbpass, $dbname);

if (!$connection) {
    die(json_encode(["success" => false, "message" => "Database connection error: " . mysqli_connect_error()]));
}
