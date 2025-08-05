<?php
require_once __DIR__ . '/../vendor/autoload.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASSWORD'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['userId'])) {
        echo json_encode(["error" => "Missing userId"]);
        exit;
    }

    $userId = $data['userId'];

    $stmt = $pdo->prepare("SELECT album FROM favourite_albums WHERE userId = :userId");
    $stmt->execute(['userId' => $userId]);

    $albums = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(["favourites" => $albums]);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
