<?php
$host = 'localhost';
$dbname = 'cm';
$username = 'root';
$password = 'Kris@1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected successfully!\n";
    echo "Database: cm\n";
} catch(PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}
?>
