<?php
header("Access-Control-Allow-Origin: http://localhost:5173"); // Allow frontend origin
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Allow HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers

// Handle pre-flight requests (for methods like PUT or POST)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit; // Exit early for OPTIONS request
}

header("Content-Type: application/json");
$data = array("message" => "This is a test message");
echo json_encode($data);
?>
