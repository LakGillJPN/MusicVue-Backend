<?php
require_once __DIR__ . '/../vendor/autoload.php';

header("Content-Type: application/json; charset=UTF-8");
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
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_GET['cognitoId'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing cognitoId"]);
        exit;
    }

    $cognitoId = $_GET['cognitoId'];

    // Get userId from CognitoId
    $userStmt = $pdo->prepare("SELECT userId FROM users WHERE CognitoId = :cognitoId");
    $userStmt->execute(['cognitoId' => $cognitoId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["error" => "User not found"]);
        exit;
    }

    $userId = $user['userId'];

    // Fetch genres
    $genresStmt = $pdo->prepare("SELECT genre FROM favourite_genres WHERE userId = :userId");
    $genresStmt->execute(['userId' => $userId]);
    $genres = $genresStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

    // Fetch artists
    $artistsStmt = $pdo->prepare("SELECT artist FROM favourite_artists WHERE userId = :userId");
    $artistsStmt->execute(['userId' => $userId]);
    $artists = $artistsStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

    // Fetch albums
    $albumsStmt = $pdo->prepare("SELECT album FROM favourite_albums WHERE userId = :userId");
    $albumsStmt->execute(['userId' => $userId]);
    $albums = $albumsStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

    echo json_encode([
        "userId" => $userId,
        "cognitoId" => $cognitoId,
        "favGenres" => $genres,
        "favArtists" => $artists,
        "favAlbums" => $albums
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
