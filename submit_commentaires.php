<?php
require_once 'includes/bdd_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_trace = intval($_POST['id_trace']);
    $auteur = trim($_POST['auteur']);
    $contenu = trim($_POST['contenu']);

    if ($id_trace && $auteur && $contenu) {
        $stmt = $pdo->prepare("INSERT INTO Commentaires_traces (id_trace, auteur, contenu) VALUES (?, ?, ?)");
        $stmt->execute([$id_trace, $auteur, $contenu]);
    }
}
