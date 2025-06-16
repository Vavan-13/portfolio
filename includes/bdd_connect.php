<?php
$host = 'localhost'; // ou '127.0.0.1'
$dbname = 'puev4583_portfolio_evan_2025';
$user = 'puev4583_user';
$password = 'PUTev1968';
$port = 3306;

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "✅ Connexion à la base réussie.";
    if (php_sapi_name() === 'cli-server') {
  echo "✅ Connexion à la base réussie.";
}
} catch (PDOException $e) {
    die("❌ Erreur de connexion à la base : " . htmlspecialchars($e->getMessage()));
}
