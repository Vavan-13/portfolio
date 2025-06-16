<?php
require 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, role, statut_validation) VALUES (?, ?, ?, ?, 'evaluateur', 0)");
    $stmt->execute([$nom, $prenom, $email, $mot_de_passe]);

    header("Location: login.php");
    exit;
}
?>

<form method="post">
  <h2>Créer un compte évaluateur</h2>
  <input name="nom" placeholder="Nom" required><br>
  <input name="prenom" placeholder="Prénom" required><br>
  <input name="email" type="email" placeholder="Email" required><br>
  <input name="mot_de_passe" type="password" placeholder="Mot de passe" required><br>
  <button type="submit">S'inscrire</button>
</form>
