<?php
require_once __DIR__ . '/includes/init.php';
require_once $basePath . 'includes/bdd_connect.php';
require_once $basePath . 'includes/sidebar.php';

// ✅ Correction ici
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['id_utilisateur']; // ✅ Correction ici
$message = "";
$messageType = "success";

$stmt = $pdo->prepare("SELECT prenom, nom, email, photo_profil FROM Utilisateurs WHERE id_utilisateur = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// PHOTO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $uploadDir = __DIR__ . '/uploads/profils/';
    $file = $_FILES['photo'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        if ($file['size'] > 5 * 1024 * 1024) {
            $message = "Fichier trop volumineux (max 5 Mo)";
            $messageType = "error";
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

            if (!isset($mimeToExt[$mime])) {
                $message = "Format non autorisé ($mime).";
                $messageType = "error";
            } else {
                $extension = $mimeToExt[$mime];
                $newFileName = 'user_' . $userId . '.' . $extension;
                $targetPath = $uploadDir . $newFileName;

                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                if (!empty($user['photo_profil']) && $user['photo_profil'] !== 'default.png') {
                    $oldPath = $uploadDir . $user['photo_profil'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $stmt = $pdo->prepare("UPDATE Utilisateurs SET photo_profil = ? WHERE id_utilisateur = ?");
                    $stmt->execute([$newFileName, $userId]);
                    $_SESSION['photo_profil'] = $newFileName;
                    $user['photo_profil'] = $newFileName;
                    $message = "Photo mise à jour avec succès.";
                } else {
                    $message = "Erreur lors de l’enregistrement du fichier.";
                    $messageType = "error";
                }
            }
        }
    } else {
        $message = "Erreur d'upload.";
        $messageType = "error";
    }
}

// INFOS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_infos'])) {
    $newPrenom = trim($_POST['new_prenom']);
    $newNom = trim($_POST['new_nom']);
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (!empty($newPrenom) && !empty($newNom)) {
        if (!empty($newPassword) && $newPassword !== $confirmPassword) {
            $message = "Les mots de passe ne correspondent pas.";
            $messageType = "error";
        } else {
            $params = [$newPrenom, $newNom];
            $sql = "UPDATE Utilisateurs SET prenom = ?, nom = ?";

            if (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                $sql .= ", mot_de_passe = ?";
                $params[] = $hashedPassword;
            }

            $sql .= " WHERE id_utilisateur = ?";
            $params[] = $userId;

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $user['prenom'] = $newPrenom;
                $user['nom'] = $newNom;
                $message = "Informations mises à jour avec succès.";
                $messageType = "success";
            } else {
                $message = "Erreur lors de la mise à jour.";
                $messageType = "error";
            }
        }
    } else {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $messageType = "error";
    }
}

$photoProfil = 'assets/medias/icone_user.png';
if (!empty($user['photo_profil']) && file_exists(__DIR__ . '/uploads/profils/' . $user['photo_profil'])) {
    $photoProfil = 'uploads/profils/' . htmlspecialchars($user['photo_profil']);
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon Profil</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="styles/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    html, body {
      margin: 0;
      height: 100dvh;
      overflow: hidden;
      background: #f5f7fa;
      font-family: 'Segoe UI', sans-serif;
    }

    main.main-content {
      padding-left: 85px;
      transition: padding-left 0.4s ease;
      height: 100dvh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .sidebar:hover ~ main.main-content {
      padding-left: 260px;
    }

    .flex-wrapper {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      max-width: 1100px;
      width: 100%;
      justify-content: center;
      padding: 20px;
    }

    .profil-container {
      flex: 1 1 400px;
      max-width: 500px;
      background: #fff;
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .profil-container h2 {
      margin-bottom: 10px;
      font-size: 1.4em;
    }

    .profil-container img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid #5a55ca;
      margin-bottom: 10px;
    }

    input[type="text"],
    input[type="password"],
    input[type="file"] {
      width: 100%;
      padding: 10px 12px;
      margin-top: 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 14px;
    }

    .password-wrapper {
      position: relative;
    }

    .password-wrapper i {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      cursor: pointer;
      color: #7c5dfa;
    }

    form button {
      display: block;
      width: 100%;
      margin-top: 20px;
      background: #5a55ca;
      color: white;
      border: none;
      padding: 12px;
      font-size: 15px;
      border-radius: 8px;
      cursor: pointer;
    }

    form button:hover {
      background: #4742b2;
    }

    .toast {
      position: fixed;
      bottom: 30px;
      right: 30px;
      padding: 14px 20px;
      border-radius: 8px;
      font-size: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      animation: slideUp 0.4s ease, fadeOut 0.5s ease 3.5s forwards;
      z-index: 9999;
    }

    .toast.success {
      background: #e7f9ed;
      color: #228b22;
      border-left: 4px solid #228b22;
    }

    .toast.error {
      background: #fdecea;
      color: #d20000;
      border-left: 4px solid #d20000;
    }

    @keyframes slideUp {
      from { transform: translateY(40px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    @keyframes fadeOut {
      to { opacity: 0; transform: translateY(20px); visibility: hidden; }
    }

    @media screen and (max-width: 768px) {
      html, body {
        overflow-y: auto;
      }

      .flex-wrapper {
        flex-direction: column;
      }

      main.main-content {
        padding-left: 85px !important;
        height: auto;
        align-items: flex-start;
        padding-top: 30px;
        padding-bottom: 30px;
      }

      .toast {
        right: 10px;
        left: 10px;
        bottom: 15px;
        text-align: center;
      }
    }
  </style>
</head>
<body>

<?php require_once 'includes/sidebar.php'; ?>

<main class="main-content">
  <div class="flex-wrapper">
    <div class="profil-container">
      <h2><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h2>
      <img src="<?= $photoProfil ?>" alt="Photo de profil">
      <p><?= htmlspecialchars($user['email']) ?></p>

      <form method="POST" enctype="multipart/form-data">
        <input type="file" name="photo" accept="image/*" required>
        <button type="submit">Changer la photo</button>
      </form>
    </div>

    <div class="profil-container">
      <h2>Modifier mes informations</h2>
      <form method="POST">
        <input type="text" name="new_prenom" placeholder="Nouveau prénom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
        <input type="text" name="new_nom" placeholder="Nouveau nom" value="<?= htmlspecialchars($user['nom']) ?>" required>

        <div class="password-wrapper">
          <input type="password" name="new_password" id="new_password" placeholder="Nouveau mot de passe">
          <i class="fa-solid fa-eye" onclick="togglePassword('new_password', this)"></i>
        </div>

        <div class="password-wrapper">
          <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirmer le mot de passe">
          <i class="fa-solid fa-eye" onclick="togglePassword('confirm_password', this)"></i>
        </div>

        <button type="submit" name="update_infos">Mettre à jour</button>
      </form>
    </div>
  </div>
</main>

<?php if (!empty($message)): ?>
  <div class="toast <?= $messageType === 'error' ? 'error' : 'success' ?>" id="toast">
    <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>

<script>
function togglePassword(id, icon) {
  const field = document.getElementById(id);
  const type = field.type === 'password' ? 'text' : 'password';
  field.type = type;
  icon.classList.toggle('fa-eye');
  icon.classList.toggle('fa-eye-slash');
}
</script>

</body>
</html>