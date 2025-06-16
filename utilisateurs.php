<?php
require_once __DIR__ . '/includes/init.php';
require_once $basePath . 'includes/bdd_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'concepteur') {
    header('Location: index.php');
    exit;
}

$modification = false;
$utilisateur_modif = null;

if (isset($_GET['modifier']) && is_numeric($_GET['modifier'])) {
    $modification = true;
    $stmt = $pdo->prepare("SELECT * FROM Utilisateurs WHERE id_utilisateur = :id");
    $stmt->execute(['id' => (int) $_GET['modifier']]);
    $utilisateur_modif = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = isset($_POST['id_utilisateur']) ? (int) $_POST['id_utilisateur'] : null;

    switch ($_POST['action']) {
        case 'accepter':
            $pdo->prepare("UPDATE Utilisateurs SET statut_validation = 1 WHERE id_utilisateur = :id")->execute(['id' => $id]);
            break;

        case 'supprimer_profil':
                        try {
                // Supprimer les messages envoyés ou reçus
                $stmt = $pdo->prepare("DELETE FROM Messages WHERE expediteur_id = :id OR destinataire_id = :id");
                $stmt->execute(['id' => $id]);

                // Supprimer les commentaires
                $stmt = $pdo->prepare("DELETE FROM Commentaires WHERE id_utilisateur = :id");
                $stmt->execute(['id' => $id]);

                // Supprimer l'utilisateur
                $stmt = $pdo->prepare("DELETE FROM Utilisateurs WHERE id_utilisateur = :id");
                $stmt->execute(['id' => $id]);

            } catch (PDOException $e) {
                echo "Erreur lors de la suppression : " . $e->getMessage();
            }

            break;

        case 'modifier_utilisateur':
            $stmt = $pdo->prepare("UPDATE Utilisateurs SET nom = :nom, prenom = :prenom, email = :email, role = :role WHERE id_utilisateur = :id");
            $stmt->execute([
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'email' => $_POST['email'],
                'role' => $_POST['role'],
                'id' => $id
            ]);
            header('Location: utilisateurs.php');
            exit;

        case 'creer_utilisateur':
            $photo = 'default.png';
            if (!empty($_FILES['photo_profil']['name']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
                $dir = __DIR__ . '/uploads/profils/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $ext = pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION);
                $photo = 'user_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['photo_profil']['tmp_name'], $dir . $photo);
            }

            $stmt = $pdo->prepare("INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, role, statut_validation, date_inscription, photo_profil) VALUES (:nom, :prenom, :email, :mdp, :role, 1, CURDATE(), :photo)");
            $stmt->execute([
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'email' => $_POST['email'],
                'mdp' => password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT),
                'role' => $_POST['role'],
                'photo' => $photo
            ]);
            header('Location: utilisateurs.php');
            exit;
    }

    header('Location: utilisateurs.php');
    exit;
}

$stmt_all = $pdo->prepare("SELECT * FROM Utilisateurs ORDER BY date_inscription DESC");
$stmt_all->execute();
$utilisateurs_tous = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gestion des utilisateurs</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="styles/style.css" />
<style>
  :root {
    --primary: #4f52ba;
    --danger: #e53935;
    --bg-light: #ffffff;
    --bg-gray: #f5f7fa;
    --shadow: rgba(0, 0, 0, 0.05);
    --border: #ddd;
    --radius: 12px;
  }

  body {
    background-color: var(--bg-gray);
    font-family: "Poppins", sans-serif;
    margin: 0;
    display: flex;
    min-height: 100dvh;
    overflow-x: hidden;
  }

  .main-content {
    flex: 1;
    padding: 2rem 2rem 2rem 100px;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    transition: padding-left 0.4s ease;
  }

  .dashboard-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
  }

  .dashboard-header h1 {
    font-size: 2.2rem;
    color: var(--primary);
    font-weight: 600;
  }

  #recherche-utilisateur {
    max-width: 400px;
    padding: 0.65rem 1rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-size: 1rem;
    background-color: #fff;
    transition: border-color 0.2s;
  }

  #recherche-utilisateur:focus {
    border-color: var(--primary);
    outline: none;
  }

  .dashboard-section {
    background-color: var(--bg-light);
    padding: 2rem;
    border-radius: var(--radius);
    box-shadow: 0 4px 12px var(--shadow);
    transition: all 0.3s ease;
  }

  .dashboard-section h2 {
    margin-bottom: 1.5rem;
    font-size: 1.6rem;
    color: var(--primary);
    font-weight: 600;
  }

  .form-section input,
  .form-section select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-size: 1rem;
    background-color: #fff;
    margin-bottom: 1rem;
    transition: border-color 0.2s;
  }

  .form-section input:focus,
  .form-section select:focus {
    border-color: var(--primary);
    outline: none;
  }

  .cards-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
  }

  .user-card {
    background: var(--bg-light);
    padding: 1.5rem;
    border-radius: var(--radius);
    box-shadow: 0 4px 12px var(--shadow);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    border-left: 5px solid var(--primary);
    transition: transform 0.2s, box-shadow 0.2s;
  }

  .user-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
  }

  .avatar-text,
  .user-card img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  .avatar-text {
    background-color: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
  }

  .user-card img {
    object-fit: cover;
    border: 2px solid var(--primary);
  }

  .actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    flex-wrap: wrap;
  }

  .btn {
    padding: 0.6rem 1.2rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.95rem;
    transition: background-color 0.2s ease-in-out, transform 0.2s;
  }

  .btn-primary {
    background-color: var(--primary);
    color: white;
  }

  .btn-danger {
    background-color: var(--danger);
    color: white;
  }

  .btn:hover {
    transform: translateY(-1px);
    opacity: 0.9;
  }

  /* Message quand aucun utilisateur ne correspond à la recherche */
  .no-results {
    text-align: center;
    font-weight: 500;
    padding: 2rem;
    color: #999;
  }

  /* Responsive layout */
  @media (max-width: 768px) {
    .main-content {
      padding: 1rem;
    }

    .dashboard-header {
      flex-direction: column;
      align-items: flex-start;
    }

    .dashboard-header h1 {
      font-size: 1.6rem;
    }
  }

  .sidebar:hover ~ .main-content {
    padding-left: 280px;
  }
</style>



</head>
<body>
  <?php require_once $basePath . 'includes/sidebar.php'; ?>
  <main class="main-content">
    <h1>Gestion des utilisateurs</h1>
    <input type="text" id="recherche-utilisateur" placeholder="Rechercher un utilisateur...">

    <form method="POST" enctype="multipart/form-data" class="form-section">
      <h2>Créer un nouvel utilisateur</h2>
      <input type="hidden" name="action" value="creer_utilisateur">
      <label>Prénom<input type="text" name="prenom" required></label>
      <label>Nom<input type="text" name="nom" required></label>
      <label>Email<input type="email" name="email" required></label>
      <label>Mot de passe<input type="password" name="mot_de_passe" required></label>
      <label>Rôle
        <select name="role" required>
          <option value="evaluateur">Évaluateur</option>
          <option value="visiteur">Visiteur</option>
        </select>
      </label>
      <label>Photo de profil<input type="file" name="photo_profil" accept="image/*"></label>
      <button type="submit" class="btn btn-primary">Créer l'utilisateur</button>
    </form>

    <?php if ($modification && $utilisateur_modif): ?>
      <form method="POST" class="form-section">
        <h2>Modifier l'utilisateur</h2>
        <input type="hidden" name="id_utilisateur" value="<?= $utilisateur_modif['id_utilisateur'] ?>">
        <input type="hidden" name="action" value="modifier_utilisateur">
        <label>Prénom<input type="text" name="prenom" value="<?= htmlspecialchars($utilisateur_modif['prenom']) ?>" required></label>
        <label>Nom<input type="text" name="nom" value="<?= htmlspecialchars($utilisateur_modif['nom']) ?>" required></label>
        <label>Email<input type="email" name="email" value="<?= htmlspecialchars($utilisateur_modif['email']) ?>" required></label>
        <label>Rôle
          <select name="role">
            <option value="concepteur" <?= $utilisateur_modif['role'] === 'concepteur' ? 'selected' : '' ?>>Concepteur</option>
            <option value="evaluateur" <?= $utilisateur_modif['role'] === 'evaluateur' ? 'selected' : '' ?>>Évaluateur</option>
            <option value="visiteur" <?= $utilisateur_modif['role'] === 'visiteur' ? 'selected' : '' ?>>Visiteur</option>
          </select>
        </label>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
      </form>
    <?php endif; ?>

    <h2>Demandes d'inscription</h2>
    <div class="cards-container">
      <?php foreach ($utilisateurs_tous as $u): if (!$u['statut_validation']): ?>
        <?php
          $photo = !empty($u['photo_profil']) && $u['photo_profil'] !== 'default.png' ? 'uploads/profils/' . htmlspecialchars($u['photo_profil']) : null;
          $initiales = strtoupper(substr($u['prenom'], 0, 1) . substr($u['nom'], 0, 1));
        ?>
        <div class="user-card">
          <?= $photo ? "<img src='$photo' alt='Photo de profil'>" : "<div class='avatar-text'>$initiales</div>" ?>
          <p><strong>Prénom :</strong> <?= htmlspecialchars($u['prenom']) ?></p>
          <p><strong>Nom :</strong> <?= htmlspecialchars($u['nom']) ?></p>
          <p><strong>Email :</strong> <?= htmlspecialchars($u['email']) ?></p>
          <p><strong>Rôle :</strong> <?= htmlspecialchars($u['role']) ?></p>
          <div class="actions">
            <form method="POST"><input type="hidden" name="id_utilisateur" value="<?= $u['id_utilisateur'] ?>"><button class="btn btn-primary" type="submit" name="action" value="accepter">Valider</button></form>
            <form method="POST" onsubmit="return confirm('Supprimer ce profil ?')"><input type="hidden" name="id_utilisateur" value="<?= $u['id_utilisateur'] ?>"><button class="btn btn-danger" type="submit" name="action" value="supprimer_profil">Supprimer</button></form>
          </div>
        </div>
      <?php endif; endforeach; ?>
    </div>

    <h2>Utilisateurs validés</h2>
    <div class="cards-container">
      <?php foreach ($utilisateurs_tous as $u): if ($u['statut_validation']): ?>
        <?php
          $photo = !empty($u['photo_profil']) && $u['photo_profil'] !== 'default.png' ? 'uploads/profils/' . htmlspecialchars($u['photo_profil']) : null;
          $initiales = strtoupper(substr($u['prenom'], 0, 1) . substr($u['nom'], 0, 1));
        ?>
        <div class="user-card">
          <?= $photo ? "<img src='$photo' alt='Photo de profil'>" : "<div class='avatar-text'>$initiales</div>" ?>
          <p><strong>Prénom :</strong> <?= htmlspecialchars($u['prenom']) ?></p>
          <p><strong>Nom :</strong> <?= htmlspecialchars($u['nom']) ?></p>
          <p><strong>Email :</strong> <?= htmlspecialchars($u['email']) ?></p>
          <p><strong>Rôle :</strong> <?= htmlspecialchars($u['role']) ?></p>
          <div class="actions">
            <form method="GET"><input type="hidden" name="modifier" value="<?= $u['id_utilisateur'] ?>"><button class="btn btn-primary" type="submit">Modifier</button></form>
            <form method="POST" onsubmit="return confirm('Supprimer ce profil ?')"><input type="hidden" name="id_utilisateur" value="<?= $u['id_utilisateur'] ?>"><button class="btn btn-danger" type="submit" name="action" value="supprimer_profil">Supprimer</button></form>
          </div>
        </div>
      <?php endif; endforeach; ?>
    </div>
  </main>

  <script>
  const searchInput = document.getElementById('recherche-utilisateur');

  searchInput.addEventListener('input', () => {
    const query = searchInput.value.toLowerCase();
    const cards = document.querySelectorAll('.user-card');
    let visibleCount = 0;

    cards.forEach(card => {
      const visible = card.innerText.toLowerCase().includes(query);
      card.style.display = visible ? 'flex' : 'none';
      if (visible) visibleCount++;
    });

    let msg = document.getElementById('no-results-msg');
    if (!msg) {
      msg = document.createElement('div');
      msg.id = 'no-results-msg';
      msg.className = 'no-results';
      msg.textContent = 'Aucun utilisateur trouvé.';
      searchInput.parentNode.appendChild(msg);
    }

    msg.style.display = visibleCount === 0 ? 'block' : 'none';
  });
</script>

</body>
</html>

