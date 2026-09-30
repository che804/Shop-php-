<?php
// Reads settings from environment variables (Render); falls back to XAMPP defaults locally.
$DB_HOST = trim(getenv('DB_HOST') ?: 'localhost');
$DB_PORT = trim(getenv('DB_PORT') ?: '3306');
$DB_NAME = trim(getenv('DB_NAME') ?: 'ecom_store');
$DB_USER = trim(getenv('DB_USER') ?: 'root');
$DB_PASS = getenv('DB_PASS') ?: '';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// Use SSL for any remote host (TiDB Cloud requires a secure connection)
$useSsl = trim((string) getenv('DB_SSL')) === '1'
    || !in_array($DB_HOST, ['localhost', '127.0.0.1'], true);

if ($useSsl) {
    foreach (['/etc/ssl/certs/ca-certificates.crt', '/etc/ssl/cert.pem'] as $ca) {
        if (file_exists($ca)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $ca;
            break;
        }
    }
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        $options
    );
} catch (PDOException $ex) {
    exit('Database connection failed: ' . $ex->getMessage());
}
