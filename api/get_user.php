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

    // Check if userId is passed as query param: /get_user.php?userId=5
    if (!isset($_GET['userId'])) {
        echo json_encode(["error" => "Missing userId"]);
        exit;
    }

    $userId = intval($_GET['userId']);

    $stmt = $pdo->prepare("SELECT userId, Username FROM Users WHERE userId = :userId");
    $stmt->execute(['userId' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode($user);
    } else {
        echo json_encode(["error" => "User not found"]);
    }

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
