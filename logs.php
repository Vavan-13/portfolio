<?php
require_once __DIR__ . '/includes/init.php';
require_once $basePath . 'includes/bdd_connect.php';
require_once $basePath . 'includes/sidebar.php';

// Connexion sécurisée à la base de données avec try/catch
try {
    $pdo = new PDO('mysql:host=localhost;dbname=puev4583_portfolio_evan_2025;charset=utf8', 'root', ''); // ⚠️ Modifie login/mot de passe
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Requête pour les logs
$stmt_logs = $pdo->query("
    SELECT l.id_log, l.action, l.date_log, u.nom, u.prenom
    FROM Logs l
    LEFT JOIN Utilisateurs u ON l.utilisateur_id = u.id_utilisateur
    ORDER BY l.date_log DESC
");

// Requête pour les messages
$stmt_msgs = $pdo->query("
    SELECT m.id_message, m.contenu, m.date_envoi, u1.prenom AS expediteur, u2.prenom AS destinataire
    FROM Messages m
    JOIN Utilisateurs u1 ON m.expediteur_id = u1.id_utilisateur
    JOIN Utilisateurs u2 ON m.destinataire_id = u2.id_utilisateur
    ORDER BY m.date_envoi DESC
");

// Requête pour les commentaires
$stmt_comments = $pdo->query("
    SELECT c.commentaire, c.date_commentaire, u.prenom, t.titre
    FROM Commentaires c
    JOIN Utilisateurs u ON c.id_utilisateur = u.id_utilisateur
    JOIN Traces t ON c.id_trace = t.id_trace
    ORDER BY c.date_commentaire DESC
");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Logs - Portfolio MMI</title>
  <link rel="stylesheet" href="styles/style.css" />
  <style>
    body { font-family: Arial, sans-serif; padding: 2rem; }
    h1, h2 { margin-top: 2rem; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background-color: #f8f8f8; }
    tr:nth-child(even) { background-color: #f2f2f2; }
  </style>
</head>
<body>
  <h1>Historique des Activités</h1>

  <h2>Logs système</h2>
  <table>
    <tr><th>Date</th><th>Utilisateur</th><th>Action</th></tr>
    <?php foreach ($stmt_logs as $log): ?>
      <tr>
        <td><?= htmlspecialchars($log['date_log']) ?></td>
        <td><?= htmlspecialchars($log['prenom'] ?? '') . ' ' . htmlspecialchars($log['nom'] ?? '') ?></td>
        <td><?= htmlspecialchars($log['action']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <h2>Messages</h2>
  <table>
    <tr><th>Date</th><th>Expéditeur</th><th>Destinataire</th><th>Contenu</th></tr>
    <?php foreach ($stmt_msgs as $msg): ?>
      <tr>
        <td><?= htmlspecialchars($msg['date_envoi']) ?></td>
        <td><?= htmlspecialchars($msg['expediteur']) ?></td>
        <td><?= htmlspecialchars($msg['destinataire']) ?></td>
        <td><?= nl2br(htmlspecialchars($msg['contenu'])) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <h2>Commentaires</h2>
  <table>
    <tr><th>Date</th><th>Auteur</th><th>Trace</th><th>Commentaire</th></tr>
    <?php foreach ($stmt_comments as $comment): ?>
      <tr>
        <td><?= htmlspecialchars($comment['date_commentaire']) ?></td>
        <td><?= htmlspecialchars($comment['prenom']) ?></td>
        <td><?= htmlspecialchars($comment['titre']) ?></td>
        <td><?= nl2br(htmlspecialchars($comment['commentaire'])) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
