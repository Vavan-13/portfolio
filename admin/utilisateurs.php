<?php
session_start();
require_once __DIR__ . '/../includes/bdd_connect.php';

// Vérifie que l'utilisateur est concepteur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'concepteur') {
    header('Location: ../index.php');
    exit;
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_utilisateur'], $_POST['action'])) {
    $id = (int) $_POST['id_utilisateur'];
    if ($_POST['action'] === 'accepter') {
        $stmt = $pdo->prepare("UPDATE Utilisateurs SET statut_validation = 1 WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $id]);
    } elseif ($_POST['action'] === 'refuser') {
        $stmt = $pdo->prepare("DELETE FROM Utilisateurs WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $id]);
    }
    header('Location: utilisateurs.php');
    exit;
}

// Récupérer les utilisateurs évaluateurs en attente
$stmt = $pdo->prepare("SELECT * FROM Utilisateurs WHERE role = 'evaluateur' AND statut_validation = 0 ORDER BY date_inscription DESC");
$stmt->execute();
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Demandes d'évaluateurs</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link rel="stylesheet" href="../styles/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<main class="main-content">
    <h1>Demandes d'inscription - Évaluateurs</h1>

    <?php if (empty($utilisateurs)) : ?>
        <p>Aucune demande en attente.</p>
    <?php else : ?>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Date d'inscription</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $u) : ?>
                    <tr>
                        <td><?= htmlspecialchars($u['nom']) ?></td>
                        <td><?= htmlspecialchars($u['prenom']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['date_inscription']) ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id_utilisateur" value="<?= $u['id_utilisateur'] ?>">
                                <button type="submit" name="action" value="accepter">✅ Accepter</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id_utilisateur" value="<?= $u['id_utilisateur'] ?>">
                                <button type="submit" name="action" value="refuser" onclick="return confirm('Refuser cette demande ?')">❌ Refuser</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
