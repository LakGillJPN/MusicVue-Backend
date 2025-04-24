<?php
require_once __DIR__ . '/../vendor/autoload.php';

header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASSWORD'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get data from frontend (JSON)
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['Username']) || !isset($data['CognitoId']) || !isset($data['Email'])) {
        echo json_encode(["error" => "Username, CognitoId, and Email are required"]);
        exit;
    }

    // Check for duplicate CognitoId
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE CognitoId = :cognitoId");
    $checkStmt->execute(['cognitoId' => $data['CognitoId']]);
    if ($checkStmt->fetchColumn() > 0) {
        echo json_encode(["error" => "CognitoId already exists"]);
    exit;
    }


    $stmt = $pdo->prepare("
        INSERT INTO Users (Username, CognitoId, Email) 
        VALUES (:username, :cognitoId, :email)
    ");
    $stmt->execute([
        'username' => $data['Username'],
        'cognitoId' => $data['CognitoId'],
        'email' => $data['Email'],
    ]);

    echo json_encode(["message" => "User added", "userId" => $pdo->lastInsertId()]);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
