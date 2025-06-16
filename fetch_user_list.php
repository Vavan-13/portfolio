<?php
require_once __DIR__ . '/includes/init.php';
require_once $basePath . 'includes/bdd_connect.php';

if (!isset($_SESSION['id_utilisateur']) || $_SESSION['role'] !== 'concepteur') {
    http_response_code(403);
    exit;
}

$concepteur_id = $_SESSION['id_utilisateur'];
$selected_user_id = $_GET['selected'] ?? null;

$stmt = $pdo->prepare("
    SELECT u.*, 
           MAX(m.date_envoi) as last_msg_date,
           SUM(CASE WHEN m.destinataire_id = :cid AND m.lu = 0 THEN 1 ELSE 0 END) AS nb_non_lus
    FROM Utilisateurs u
    JOIN Messages m ON (u.id_utilisateur = m.expediteur_id OR u.id_utilisateur = m.destinataire_id)
    WHERE u.role IN ('visiteur', 'evaluateur')
      AND (m.expediteur_id = :cid OR m.destinataire_id = :cid)
      AND u.id_utilisateur != :cid
    GROUP BY u.id_utilisateur
    ORDER BY last_msg_date DESC
");
$stmt->execute(['cid' => $concepteur_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    $classes = [];
    if ($user['id_utilisateur'] == $selected_user_id) $classes[] = 'active';
    if ($user['nb_non_lus'] > 0 && $user['id_utilisateur'] != $selected_user_id) {
        $classes[] = 'unread';
    }

    $initiales = strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1));
    $photo_profil = !empty($user['photo_profil']) && $user['photo_profil'] !== 'default.png'
        ? 'uploads/profils/' . htmlspecialchars(basename($user['photo_profil']))
        : null;

    echo '<li class="' . implode(' ', $classes) . '">';
    echo '<a href="messagerie_admin.php?uid=' . $user['id_utilisateur'] . '" style="display: flex; align-items: center; gap: 10px;">';

    // Avatar
    if ($photo_profil && file_exists($photo_profil)) {
        echo '<img src="' . $photo_profil . '" alt="Profil" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">';
    } else {
        echo '<div class="avatar-text" style="width: 36px; height: 36px;">' . htmlspecialchars($initiales) . '</div>';
    }

    // Infos utilisateur
    echo '<div style="display: flex; flex-direction: column;">';
    echo '<strong>' . htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . '</strong>';

    if ($user['nb_non_lus'] > 0 && $user['id_utilisateur'] != $selected_user_id) {
        echo ' <span class="badge">' . $user['nb_non_lus'] . '</span>';
    }

    echo '<small>' . htmlspecialchars($user['email']) . '</small>';
    echo '</div>';

    echo '</a></li>';
}
