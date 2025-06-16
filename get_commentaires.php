<?php
session_start();
require_once __DIR__ . '/includes/init.php';
require_once $basePath . 'includes/bdd_connect.php';

$id_trace = intval($_GET['id'] ?? 0);

if (!$id_trace) {
    echo "<p>Trace invalide.</p>";
    exit;
}

// Récupère les commentaires
$stmt = $pdo->prepare("
    SELECT C.commentaire, C.date_commentaire, U.nom, U.prenom
    FROM Commentaires C
    JOIN Utilisateurs U ON C.id_utilisateur = U.id_utilisateur
    WHERE C.id_trace = ?
    ORDER BY C.date_commentaire DESC
");
$stmt->execute([$id_trace]);
$commentaires = $stmt->fetchAll();

?>

<h2>Commentaires</h2>

<?php if (empty($commentaires)): ?>
  <p>Aucun commentaire pour cette trace.</p>
<?php else: ?>
  <?php foreach ($commentaires as $c): ?>
    <div style="border-bottom: 1px solid #ddd; margin-bottom: 10px; padding-bottom: 10px;">
      <strong><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></strong>
      <small style="color: gray;"> le <?= date('d/m/Y à H:i', strtotime($c['date_commentaire'])) ?></small>
      <p><?= nl2br(htmlspecialchars($c['commentaire'])) ?></p>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php if (in_array($_SESSION['role'], ['evaluateur', 'concepteur'])): ?>
  <form method="POST" action="poster_commentaire.php">
    <input type="hidden" name="id_trace" value="<?= $id_trace ?>">
    <textarea name="commentaire" placeholder="Ajouter un commentaire..." required></textarea>
    <button type="submit">Envoyer</button>
  </form>
<?php endif; ?>
