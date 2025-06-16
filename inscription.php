<?php
session_start();
require_once __DIR__ . '/includes/bdd_connect.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['password'] ?? '';
    $motDePasseConfirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'visiteur';
    $role = in_array($role, ['visiteur', 'évaluateur']) ? $role : 'visiteur';
    $statut_validation = ($role === 'évaluateur') ? 0 : 1;



    $photoProfilName = 'default.png';

    if ($nom && $prenom && $email && $motDePasse && $motDePasseConfirm) {
        if (!in_array($role, ['visiteur', 'évaluateur'])) {
            $erreur = 'Rôle invalide.';
        } elseif ($motDePasse !== $motDePasseConfirm) {
            $erreur = 'Les mots de passe ne correspondent pas.';
        } else {
            $stmt = $pdo->prepare("SELECT id_utilisateur FROM Utilisateurs WHERE email = :email");
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                $erreur = 'Un compte avec cet email existe déjà.';
            } else {
                if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['photo_profil']['tmp_name'];
                    $uploadDir = __DIR__ . '/uploads/profils/';

                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($tmpName);
                    $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

                    if (isset($mimeToExt[$mime])) {
                        $extension = $mimeToExt[$mime];
                        $photoProfilName = 'user_' . time() . '.' . $extension;
                        $destination = $uploadDir . $photoProfilName;
                        move_uploaded_file($tmpName, $destination);
                    }
                }

                $hash = password_hash($motDePasse, PASSWORD_DEFAULT);

               $insert = $pdo->prepare("INSERT INTO Utilisateurs 
                  (nom, prenom, email, mot_de_passe, role, statut_validation, date_inscription, photo_profil) 
                  VALUES (:nom, :prenom, :email, :mdp, :role, :statut_validation, NOW(), :photo)");

              $insert->execute([
                  'nom' => $nom,
                  'prenom' => $prenom,
                  'email' => $email,
                  'mdp' => $hash,
                  'role' => $role,
                  'statut_validation' => $statut_validation,
                  'photo' => $photoProfilName
              ]);


                $stmt = $pdo->prepare("SELECT * FROM Utilisateurs WHERE email = :email");
                $stmt->execute(['email' => $email]);
                $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($utilisateur) {
                    $_SESSION['prenom'] = $utilisateur['prenom'];
                    $_SESSION['nom'] = $utilisateur['nom']; // ← si tu l’utilises
                    $_SESSION['role'] = $utilisateur['role'];
                    $_SESSION['id_utilisateur'] = $utilisateur['id_utilisateur'];
                    $_SESSION['id'] = $utilisateur['id_utilisateur']; // (optionnel)

                    $_SESSION['photo_profil'] = $utilisateur['photo_profil'] ?? null;
                    $_SESSION['statut_validation'] = $utilisateur['statut_validation']; // ← très important


                    $prenom = htmlspecialchars($utilisateur['prenom']);
                    echo <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="refresh" content="5;url=index.php">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Compte créé avec succès</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins&display=swap">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body { background-color: #f0f4ff; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 2rem; }
    .success-box { background: #ffffff; padding: 2.5rem 3rem; border-radius: 20px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06); text-align: center; max-width: 500px; width: 100%; animation: fadeIn 0.4s ease-out; }
    .checkmark { width: 72px; height: 72px; margin: 0 auto 1rem; }
    .success-box h1 { font-size: 1.7rem; font-weight: 600; color: #161a2d; margin-bottom: 0.5rem; }
    .success-box p { font-size: 1rem; color: #444; margin-bottom: 0.5rem; }
    .success-box a { display: inline-block; margin-top: 0.5rem; color: #4f52ba; font-weight: 500; text-decoration: underline; transition: color 0.2s ease; }
    .success-box a:hover { color: #3b3e99; }
    .progress-bar { margin-top: 1.5rem; background-color: #e0e0e0; height: 8px; border-radius: 6px; overflow: hidden; }
    .progress-bar .bar { height: 100%; width: 100%; background-color: #4f52ba; animation: loadProgress 5s linear forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes loadProgress { from { width: 0%; } to { width: 100%; } }
    @media (max-width: 480px) { .success-box { padding: 2rem 1.5rem; } .success-box h1 { font-size: 1.4rem; } }
  </style>
</head>
<body>
  <div class="success-box">
    <svg class="checkmark" viewBox="0 0 52 52">
      <circle cx="26" cy="26" r="25" fill="none" stroke="#4caf50" stroke-width="2"/>
      <path fill="none" stroke="#4caf50" stroke-width="4" d="M14 27 l7 7 l17 -17" />
    </svg>
    <h1>Compte créé avec succès !</h1>
    <p>Bienvenue, {$prenom} 👋</p>
    <p>Vous allez être redirigé automatiquement vers votre compte...</p>
    <p><a href="index.php">Cliquez ici si la redirection ne fonctionne pas</a></p>
    <div class="progress-bar">
      <div class="bar"></div>
    </div>
  </div>
</body>
</html>
HTML;
                    exit;
                } else {
                    $erreur = 'Une erreur est survenue lors de la connexion automatique.';
                }
            }
        }
    } else {
        $erreur = 'Veuillez remplir tous les champs.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription - Portfolio MMI</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="styles/style.css" />
  <style>
    .main-content {
      margin-left: 85px;
      padding: 2rem;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: margin-left 0.4s ease;
    }
    .sidebar:hover ~ .main-content {
      margin-left: 260px;
    }
    .register-container {
      width: 100%;
      max-width: 500px;
      background: #fff;
      padding: 2rem;
      border-radius: 16px;
      box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    }
    .register-container h1 {
      text-align: center;
      font-size: 1.8rem;
      margin-bottom: 1.5rem;
      color: #161a2d;
    }
    label {
      font-weight: 500;
      display: block;
      margin-bottom: 0.5rem;
      margin-top: 1rem;
    }
    input, select {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
      background-color: #f9fafb;
    }
    input[type="file"] {
      padding: 0.4rem;
    }
    .password-container {
      position: relative;
    }
    .toggle-password {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      cursor: pointer;
      color: #3b3e99;
    }
    button {
      margin-top: 1.5rem;
      width: 100%;
      background-color: #4f52ba;
      color: white;
      padding: 0.75rem;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #3b3e99;
    }
    .error-message {
      color: red;
      margin-top: 1rem;
      text-align: center;
    }
    .login-link {
      text-align: center;
      margin-top: 1.5rem;
      font-size: 0.95rem;
    }
    .login-link a {
      color: #4f52ba;
      text-decoration: none;
      font-weight: 600;
    }
    @media (max-width: 768px) {
      .main-content {
        margin-left: 85px;
        padding: 1.5rem;
      }
      .register-container {
        padding: 1.5rem;
      }
    }
  </style>
</head>
<body>
<?php require_once 'includes/sidebar.php'; ?>
<main class="main-content">
  <div class="register-container">
    <h1>Créer un compte</h1>
    <?php if ($erreur): ?>
      <p class="error-message"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>
    <form method="POST" action="inscription.php" enctype="multipart/form-data">
      <label for="prenom">Prénom</label>
      <input type="text" name="prenom" id="prenom" required>
      <label for="nom">Nom</label>
      <input type="text" name="nom" id="nom" required>
      <label for="email">Email</label>
      <input type="email" name="email" id="email" required>
      <label for="password">Mot de passe</label>
      <div class="password-container">
        <input type="password" name="password" id="password" required>
        <span class="material-icons toggle-password" onclick="togglePassword('password', this)">visibility</span>
      </div>
      <label for="confirm_password">Confirmer le mot de passe</label>
      <div class="password-container">
        <input type="password" name="confirm_password" id="confirm_password" required>
        <span class="material-icons toggle-password" onclick="togglePassword('confirm_password', this)">visibility</span>
      </div>
      <label for="role">Je suis</label>
      <select name="role" id="role" required>
        <option value="visiteur">Visiteur</option>
        <option value="évaluateur">Évaluateur</option>
      </select>
      <label for="photo_profil">Photo de profil</label>
      <input type="file" name="photo_profil" id="photo_profil" accept="image/*">
      <button type="submit">Créer mon compte</button>
    </form>
    <div class="login-link">
      Déjà un compte ? <a href="login.php">Se connecter</a>
    </div>
  </div>
</main>
<script>
function togglePassword(fieldId, iconElement) {
  const field = document.getElementById(fieldId);
  if (field.type === "password") {
    field.type = "text";
    iconElement.textContent = "visibility_off";
  } else {
    field.type = "password";
    iconElement.textContent = "visibility";
  }
}
</script>
</body>
</html>
