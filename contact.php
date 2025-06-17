<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_error.log');
error_reporting(E_ALL);

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/bdd_connect.php';
require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';
$config = require __DIR__ . '/includes/config_mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$succes = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['url'])) {
        die("Bot détecté.");
    }

    $nom = htmlspecialchars(trim($_POST['nom'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $objet = html_entity_decode(htmlspecialchars(trim($_POST['objet'] ?? ''), ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
    $message = strip_tags(trim($_POST['message'] ?? ''));

    if ($nom && filter_var($email, FILTER_VALIDATE_EMAIL) && $objet && $message) {
        try {
            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->setLanguage('fr', __DIR__ . '/phpmailer/language/');
            $mail->isSMTP();
            $mail->Host = 'mail.evan-puthommerostane.fr';
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_user'];
            $mail->Password = $config['smtp_pass'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom($config['smtp_user'], 'Formulaire de contact');
            $mail->addAddress($config['smtp_user']);
            $mail->addReplyTo($email, $nom);

            $mail->Subject = "Message de $nom : $objet";
            $mail->Body = "Nom : $nom\nEmail : $email\nObjet : $objet\n\n$message";
            $mail->send();

            $confirmation = new PHPMailer(true);
            $confirmation->CharSet = 'UTF-8';
            $confirmation->setLanguage('fr', __DIR__ . '/phpmailer/language/');
            $confirmation->isSMTP();
            $confirmation->Host = 'mail.evan-puthommerostane.fr';
            $confirmation->SMTPAuth = true;
            $confirmation->Username = $config['smtp_user'];
            $confirmation->Password = $config['smtp_pass'];
            $confirmation->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $confirmation->Port = 465;

            $confirmation->setFrom($config['smtp_user'], 'Portfolio Evan');
            $confirmation->addAddress($email, $nom);
            $confirmation->Subject = "Confirmation de réception – $objet";
            $confirmation->Body = "Bonjour $nom,\n\nJ'ai bien reçu votre message :\n\n\"$message\"\n\nJe reviens vers vous rapidement.\n\nCordialement,\nEvan Puthomme-Rostane";
            $confirmation->send();

            $succes = "✅ Message envoyé. Une confirmation vous a été envoyée.";
        } catch (Exception $e) {
            $erreur = "❌ Erreur lors de l'envoi : " . $e->getMessage();
        }
    } else {
        $erreur = "❌ Tous les champs sont obligatoires et l'email doit être valide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Me contacter</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="styles/style.css" />
  <style>
    .main-content {
      margin-left: 85px;
      padding: 2rem;
      min-height: 100vh;
      transition: margin-left 0.4s ease;
      background-color: #f0f4ff;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .sidebar:hover ~ .main-content {
      margin-left: 260px;
    }

    .contact-form-container {
      max-width: 600px;
      width: 100%;
      background: #fff;
      padding: 2rem;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    }

    h1 {
      text-align: center;
      margin-bottom: 1.5rem;
      font-size: 1.8rem;
      color: #161a2d;
    }

    label {
      display: block;
      margin: 1rem 0 0.4rem;
      font-weight: 600;
    }

    input, textarea {
      width: 100%;
      padding: 0.75rem 1rem;
      font-size: 1rem;
      border-radius: 8px;
      border: 1px solid #ccc;
      background-color: #f9fafb;
    }

    textarea {
      resize: vertical;
    }

    .feedback {
      text-align: center;
      margin-bottom: 1rem;
      font-weight: 600;
    }

    .feedback.success {
      color: #2e7d32;
    }

    .feedback.error {
      color: #c62828;
    }

    button {
      margin-top: 1.5rem;
      width: 100%;
      background-color: #4f52ba;
      color: white;
      padding: 0.75rem;
      font-size: 1rem;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #3b3e99;
    }

    @media (max-width: 768px) {
      .main-content {
        margin-left: 85px;
        padding: 1rem;
      }

      .contact-form-container {
        padding: 1.5rem;
      }
    }
  </style>
</head>
<body>
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="main-content">
  <div class="contact-form-container">
    <h1>Me Contacter</h1>

    <?php if ($succes): ?>
      <p class="feedback success"><?= htmlspecialchars($succes) ?></p>
    <?php elseif ($erreur): ?>
      <p class="feedback error"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <form method="POST">
      <input type="text" name="url" style="display:none">

      <label for="nom">Votre nom :</label>
      <input type="text" id="nom" name="nom" required>

      <label for="email">Votre adresse e-mail :</label>
      <input type="email" id="email" name="email" required>

      <label for="objet">Objet du message :</label>
      <input type="text" id="objet" name="objet" required>

      <label for="message">Votre message :</label>
      <textarea id="message" name="message" rows="6" required></textarea>

      <button type="submit">Envoyer le message</button>
    </form>
  </div>
</main>
</body>
</html>
