<?php
session_start();
require_once __DIR__ . '/includes/init.php';
require_once $basePath . 'includes/bdd_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_SESSION['role'], ['evaluateur', 'concepteur'])) {
    $id_trace = intval($_POST['id_trace'] ?? 0);
    $commentaire = trim($_POST['commentaire'] ?? '');
    $id_utilisateur = $_SESSION['id_utilisateur'] ?? null;

    if ($id_trace && $commentaire && $id_utilisateur) {
        $stmt = $pdo->prepare("INSERT INTO Commentaires (id_trace, id_utilisateur, commentaire, date_commentaire) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$id_trace, $id_utilisateur, $commentaire]);
    }
}

header("Location: mes_realisations.php");
exit;
