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

    if (empty($data['albums']) || !is_array($data['albums'])) {
        echo json_encode(["error" => "Missing or invalid albums array"]);
        exit;
    }

    $userId = $data['userId'];
    $albums = $data['albums'];

    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM favourite_albums WHERE userId = :userId AND album = :album");
    $deleteStmt = $pdo->prepare("DELETE FROM favourite_albums WHERE userId = :userId AND album = :album");

    foreach ($albums as $album) {
        $checkStmt->execute(['userId' => $userId, 'album' => $album]);
        if ($checkStmt->fetchColumn() > 0) {
            $deleteStmt->execute(['userId' => $userId, 'album' => $album]);
        }
    }

    echo json_encode(["message" => "Albums deleted successfully"]);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
