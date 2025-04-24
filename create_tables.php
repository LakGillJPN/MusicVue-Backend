<?php
// Include the Composer autoload file
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASSWORD'];

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create the database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db`");
    echo "Database created or already exists.<br>";

    // Connect to the newly created database
    $pdo->exec("USE `$db`");

    // Create 'users' table 
    $tableSql2 = "CREATE TABLE IF NOT EXISTS users (
        userId INT AUTO_INCREMENT PRIMARY KEY,
        Username VARCHAR(100) NOT NULL UNIQUE,
        CognitoId VARCHAR(100) NOT NULL UNIQUE,
        Email VARCHAR(100) NOT NULL
    )";
    $pdo->exec($tableSql2);
    echo "Table 'users' created successfully.<br>";

    // Create 'favourites' table
    $tableSql3 = "CREATE TABLE IF NOT EXISTS favourites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        userId INT NOT NULL,
        favGenres TEXT,
        favArtists TEXT,
        favAlbums TEXT,
        FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE
    )";
    $pdo->exec($tableSql3);
    echo "Table 'favourites' created successfully.<br>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
