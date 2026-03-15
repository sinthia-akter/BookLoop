<?php

header("Content-Type: application/json");

function sendResponse($data,$status=200){
http_response_code($status);
echo json_encode($data);
exit();
}

function getJsonInput(){
return json_decode(file_get_contents("php://input"),true);
}

?>