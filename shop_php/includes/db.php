<?php
// Local (XAMPP) defaults. On Render, set these as Environment Variables instead
// (Dashboard > your service > Environment) and this file uses them automatically.
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_PORT = getenv('DB_PORT') ?: '3306';
$DB_NAME = getenv('DB_NAME') ?: 'ecom_store';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';

// TiDB Cloud (and some other hosted MySQL) require TLS. Set DB_SSL=1 to turn it on;
// it uses the container's system CA bundle, which already trusts TiDB Cloud's certificate.
$DB_SSL = getenv('DB_SSL') === '1';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
if ($DB_SSL) {
    $caFile = getenv('DB_SSL_CA') ?: '/etc/ssl/certs/ca-certificates.crt';
    if (is_file($caFile)) $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
}

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        $options
    );
} catch (PDOException $ex) {
    http_response_code(500);
    exit('Database connection failed. Check DB_HOST/DB_PORT/DB_NAME/DB_USER/DB_PASS/DB_SSL.'
       . (getenv('APP_DEBUG') === '1' ? ' Detail: ' . htmlspecialchars($ex->getMessage(), ENT_QUOTES, 'UTF-8') : ''));
}
