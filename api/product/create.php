<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// get database connection
include_once '../config/database.php';

// instantiate product object
include_once '../objects/request.php';

$database = new Database();
$db = $database->getConnection();

$request = new Request($db, $_GET['table']);

// get posted data
$data = $data = json_decode(file_get_contents("php://input"),true);

// make sure data is not empty
foreach ($request->fields as $field) {
    if($field == 'ID' || $field == 'Country'){
        continue;
    }
    else if (empty($data[$field])){
        $data[$field] = 0;
    }
}

if ($data['Country'] != NULL) {
    // create the product
    if ($request->create($data)) {

        // set response code - 201 created
        http_response_code(201);

        // tell the user
        echo json_encode(array("message" => "Record was created."));
    }

    // if unable to create the product, tell the user
    else {

        // set response code - 503 service unavailable
        http_response_code(503);

        // tell the user
        echo json_encode(array("message" => "Unable to create record."));
    }
}

// tell the user data is incomplete
else {

    // set response code - 400 bad request
    http_response_code(400);

    // tell the user
    echo json_encode(array("message" => "Unable to create record. Data is incomplete."));
}
