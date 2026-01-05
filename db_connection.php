<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load .env variables
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

try {
    $username = $_ENV['MONGO_DB_USERNAME'];
    $password = $_ENV['MONGO_DB_PASSWORD'];
    $dbName   = $_ENV['MONGO_DB_NAME'];

    if (!$username || !$password || !$dbName) {
        throw new Exception("Missing MongoDB credentials");
    }

    $uri = "mongodb+srv://{$username}:{$password}@pandit.1lmkyi2.mongodb.net/?retryWrites=true&w=majority";
    $client = new MongoDB\Client($uri);

    // Select database
    $database = $client->selectDatabase($dbName);

    // Collections
    $usersCollection = $database->selectCollection('panditcollection');
    $bookingsCollection = $database->selectCollection('bookings');

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]);
    exit;
}
?>
