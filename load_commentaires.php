<?php
require_once 'includes/bdd_connect.php';
$id = intval($_GET['id_trace']);
$req = $pdo->prepare("SELECT auteur, contenu, date_commentaire FROM Commentaires_traces WHERE id_trace = ? ORDER BY date_commentaire DESC");
$req->execute([$id]);
foreach ($req as $c) {
    echo "<p><strong>{$c['auteur']}</strong> ({$c['date_commentaire']})<br>" . nl2br(htmlspecialchars($c['contenu'])) . "</p><hr>";
}
