<?php
session_start();
require_once __DIR__ . '/includes/bdd_connect.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['password'] ?? '';

    if (!empty($email) && !empty($motDePasse)) {
        $stmt = $pdo->prepare("SELECT * FROM Utilisateurs WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
          $_SESSION['prenom'] = $utilisateur['prenom'];
          $_SESSION['role'] = $utilisateur['role'];
          $_SESSION['id_utilisateur'] = $utilisateur['id_utilisateur']; // ✅ NOM COHÉRENT avec mes_realisations.php
          $_SESSION['photo_profil'] = $utilisateur['photo_profil'] ?? null;
          $_SESSION['statut_validation'] = (int)$utilisateur['statut_validation']; // ✅ TRÈS IMPORTANT

          header("Location: index.php");
          exit;
        } else {
            $erreur = 'Email ou mot de passe incorrect';
        }
    } else {
        $erreur = 'Veuillez remplir tous les champs';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Connexion - Portfolio MMI</title>
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

    .login-container {
      width: 100%;
      max-width: 420px;
      background: #fff;
      padding: 2rem;
      border-radius: 16px;
      box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    }

    .login-container h1 {
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

    input {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
      background-color: #f9fafb;
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
    color: #3b3e99; /* <-- couleur personnalisée */
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

    .register-link {
      text-align: center;
      margin-top: 1.5rem;
      font-size: 0.95rem;
    }

    .register-link a {
      color: #4f52ba;
      text-decoration: none;
      font-weight: 600;
    }

    @media (max-width: 768px) {
      .main-content {
        margin-left: 85px;
        padding: 1.5rem;
      }

      .login-container {
        padding: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="login-container">
      <h1>Connexion</h1>

      <?php if (!empty($erreur)) : ?>
        <p class="error-message"><?= htmlspecialchars($erreur) ?></p>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Mot de passe</label>
        <div class="password-container">
          <input type="password" name="password" id="password" required>
          <span class="material-icons toggle-password" onclick="togglePassword()">visibility</span>
        </div>

        <button type="submit">Se connecter</button>
      </form>

      <div class="register-link">
        Pas encore inscrit ? <a href="inscription.php">Créer un compte</a>
      </div>
    </div>
  </main>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById("password");
      const toggleIcon = document.querySelector(".toggle-password");

      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.textContent = "visibility_off";
      } else {
        passwordInput.type = "password";
        toggleIcon.textContent = "visibility";
      }
    }
  </script>
</body>
</html>
