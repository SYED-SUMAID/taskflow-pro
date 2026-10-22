
<?php

$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'taskflow';
$user = getenv('DB_USER') ?: 'taskflow_user';
$password = getenv('DB_PASSWORD') ?: 'taskflow@6';

$dsn = "pgsql:host=$host;dbname=$dbname";

try {
    $pdo = new PDO($dsn, $user, $password);

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());

    http_response_code(500);
    exit("A database error occurred.");
}
