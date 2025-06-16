<?php
require_once __DIR__ . '/includes/init.php';
require_once $basePath . 'includes/bdd_connect.php';

if (!isset($_SESSION['id_utilisateur']) || !isset($_SESSION['role'])) {
    http_response_code(403);
    exit('Non autorisé');
}

$id = $_SESSION['id_utilisateur'];
$role = $_SESSION['role'];
$messages = [];

if ($role === 'concepteur') {
    $uid = $_GET['uid'] ?? null;
    if (!$uid) {
        exit('<p>Aucune conversation sélectionnée.</p>');
    }

    $stmt = $pdo->prepare("
        SELECT m.*, u.prenom, u.nom, u.photo_profil
        FROM Messages m
        JOIN Utilisateurs u ON u.id_utilisateur = m.expediteur_id
        WHERE (m.expediteur_id = :uid AND m.destinataire_id = :cid)
           OR (m.expediteur_id = :cid AND m.destinataire_id = :uid)
        ORDER BY m.date_envoi ASC
    ");
    $stmt->execute(['uid' => $uid, 'cid' => $id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif (in_array($role, ['visiteur', 'evaluateur'])) {
    $stmt = $pdo->query("SELECT id_utilisateur, prenom, nom FROM Utilisateurs WHERE role = 'concepteur'");
    $concepteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $concepteur_ids = array_column($concepteurs, 'id_utilisateur');

    if (!empty($concepteur_ids)) {
        $placeholders = implode(',', array_fill(0, count($concepteur_ids), '?'));
        $sql = "
            SELECT MIN(m.id_message) as id_message, m.contenu, m.date_envoi, m.expediteur_id,
                   u.prenom, u.nom, u.photo_profil
            FROM Messages m
            JOIN Utilisateurs u ON u.id_utilisateur = m.expediteur_id
            WHERE (m.expediteur_id = ? AND m.destinataire_id IN ($placeholders))
               OR (m.expediteur_id IN ($placeholders) AND m.destinataire_id = ?)
            GROUP BY m.contenu, m.date_envoi, m.expediteur_id, u.prenom, u.nom, u.photo_profil
            ORDER BY m.date_envoi ASC
        ";
        $params = array_merge([$id], $concepteur_ids, $concepteur_ids, [$id]);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

foreach ($messages as $msg) {
    $classe = $msg['expediteur_id'] == $id ? 'envoye' : 'recu';
    $initiales = strtoupper(substr($msg['prenom'], 0, 1) . substr($msg['nom'], 0, 1));
    $photo_profil = !empty($msg['photo_profil']) && $msg['photo_profil'] !== 'default.png'
        ? 'uploads/profils/' . htmlspecialchars(basename($msg['photo_profil']))
        : null;

    echo '<div class="message-wrapper ' . $classe . '">';
    echo '<div class="avatar">';
    if ($photo_profil) {
        echo '<img src="' . $photo_profil . '" alt="Profil">';
    } else {
        echo '<div class="avatar-text">' . htmlspecialchars($initiales) . '</div>';
    }
    echo '</div>';

    echo '<div class="message ' . $classe . '">';
    echo '<strong>' . htmlspecialchars($msg['prenom']) . ':</strong><br>';
    echo nl2br(htmlspecialchars($msg['contenu']));
    echo '<small>' . date('d/m/Y H:i', strtotime($msg['date_envoi'])) . '</small>';
    echo '</div>';
    echo '</div>';
}
