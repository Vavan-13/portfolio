<?php
require 'includes/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    $stmt = $pdo->prepare("SELECT * FROM Utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        if ($user['role'] === 'evaluateur' && !$user['statut_validation']) {
            echo \"Votre compte n'a pas encore été validé.\";
        } else {
            $_SESSION['utilisateur'] = $user;
            header('Location: index.php');
            exit;
        }
    } else {
        echo \"Identifiants incorrects.\";
    }
}
?>

<form method="post">
  <h2>Connexion</h2>
  <input name="email" type="email" placeholder="Email" required><br>
  <input name="mot_de_passe" type="password" placeholder="Mot de passe" required><br>
  <button type="submit">Se connecter</button>
</form>
