<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'medihub_db';

try {
    $dsn = "mysql:host={$host};port=3306";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Database '{$db}' ensured.\n";
} catch (PDOException $e) {
    echo "DB create error: " . $e->getMessage() . "\n";
    exit(1);
}
