<?php
require_once __DIR__ . '/../vendor/autoload.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASSWORD'];

try {
    // Connect to DB
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if cognitoId is passed as query param: /get_user.php?cognitoId=abc123
    if (!isset($_GET['cognitoId'])) {
        echo json_encode(["error" => "Missing cognitoId"]);
        exit;
    }

    $cognitoId = $_GET['cognitoId'];

    $stmt = $pdo->prepare("SELECT userId, Username, CognitoId, Email FROM Users WHERE CognitoId = :cognitoId");
    $stmt->execute(['cognitoId' => $cognitoId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode($user);
    } else {
        echo json_encode(["error" => "User not found"]);
    }

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
