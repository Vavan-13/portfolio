<?php
require 'includes/config.php';
if (isset($_POST['id_utilisateur'])) {
    $id = (int) $_POST['id_utilisateur'];
    $pdo->prepare("UPDATE Utilisateurs SET statut_validation = 1 WHERE id_utilisateur = ?")->execute([$id]);
}
header('Location: admin_utilisateurs.php');
