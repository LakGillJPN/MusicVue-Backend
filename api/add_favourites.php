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

    // Insert genres without duplicates
    if (!empty($data['favGenres']) && is_array($data['favGenres'])) {
        $checkGenre = $pdo->prepare("SELECT COUNT(*) FROM favourite_genres WHERE userId = :userId AND genre = :genre");
        $insertGenre = $pdo->prepare("INSERT INTO favourite_genres (userId, genre) VALUES (:userId, :genre)");

        foreach ($data['favGenres'] as $genre) {
            $checkGenre->execute(['userId' => $userId, 'genre' => $genre]);
            if ($checkGenre->fetchColumn() == 0) {
                $insertGenre->execute(['userId' => $userId, 'genre' => $genre]);
            }
        }
    }

    // Insert artists without duplicates
    if (!empty($data['favArtists']) && is_array($data['favArtists'])) {
        $checkArtist = $pdo->prepare("SELECT COUNT(*) FROM favourite_artists WHERE userId = :userId AND artist = :artist");
        $insertArtist = $pdo->prepare("INSERT INTO favourite_artists (userId, artist) VALUES (:userId, :artist)");

        foreach ($data['favArtists'] as $artist) {
            $checkArtist->execute(['userId' => $userId, 'artist' => $artist]);
            if ($checkArtist->fetchColumn() == 0) {
                $insertArtist->execute(['userId' => $userId, 'artist' => $artist]);
            }
        }
    }

    // Insert albums without duplicates
    if (!empty($data['favAlbums']) && is_array($data['favAlbums'])) {
        $checkAlbum = $pdo->prepare("SELECT COUNT(*) FROM favourite_albums WHERE userId = :userId AND album = :album");
        $insertAlbum = $pdo->prepare("INSERT INTO favourite_albums (userId, album) VALUES (:userId, :album)");

        foreach ($data['favAlbums'] as $album) {
            $checkAlbum->execute(['userId' => $userId, 'album' => $album]);
            if ($checkAlbum->fetchColumn() == 0) {
                $insertAlbum->execute(['userId' => $userId, 'album' => $album]);
            }
        }
    }

    echo json_encode(["message" => "Favourites added successfully"]);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
