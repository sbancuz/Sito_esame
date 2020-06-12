<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
include_once '../config/database.php';
include_once '../objects/request.php';

// instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// initialize object
$request = new Request($db, $_GET['table']);

// check if more than 0 record found
if($request->fields != NULL){

    
    // set response code - 200 OK
    http_response_code(200);

    // show products data in json format

    echo json_encode($request->fields);
    
}else{

    // set response code - 404 Not found
    http_response_code(404);
    
    // tell the user no products found
    echo json_encode(
        array("message" => "No products found.")
    );
}