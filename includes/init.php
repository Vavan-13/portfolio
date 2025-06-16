<?php
// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['id_utilisateur']) && $_SESSION['role'] === 'evaluateur') {
    // charger statut_validation depuis la base si non défini
    if (!isset($_SESSION['statut_validation'])) {
        $stmt = $pdo->prepare("SELECT statut_validation FROM Utilisateurs WHERE id_utilisateur = ?");
        $stmt->execute([$_SESSION['id_utilisateur']]);
        $_SESSION['statut_validation'] = (int)$stmt->fetchColumn();
    }
}


// Détection du dossier courant pour adapter les chemins relatifs
$basePath = (str_contains($_SERVER['SCRIPT_FILENAME'], '/admin/')) ? '../' : './';

// Chargement de la connexion à la base
require_once $basePath . 'includes/bdd_connect.php';

// Définition de variables utiles globales
$role = $_SESSION['role'] ?? 'visiteur';
$prenom = $_SESSION['prenom'] ?? 'Non connecté';

// AJOUT : charger statut_validation si l'utilisateur est évaluateur connecté
if (!isset($_SESSION['statut_validation']) && isset($_SESSION['id_utilisateur']) && $_SESSION['role'] === 'evaluateur') {
    $stmt = $pdo->prepare("SELECT statut_validation FROM Utilisateurs WHERE id_utilisateur = ?");
    $stmt->execute([$_SESSION['id_utilisateur']]);
    $_SESSION['statut_validation'] = (int)$stmt->fetchColumn();
}
