<?php
require_once __DIR__ . '/includes/init.php';
require_once $basePath . 'includes/bdd_connect.php';

if (!isset($_SESSION['id_utilisateur']) || !in_array($_SESSION['role'], ['visiteur', 'evaluateur'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['id_utilisateur'];

$stmt = $pdo->query("SELECT id_utilisateur, prenom, nom FROM Utilisateurs WHERE role = 'concepteur'");
$concepteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
$concepteur_ids = array_column($concepteurs, 'id_utilisateur');

// Envoi de message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if ($message !== '' && !empty($concepteur_ids)) {
        $stmt = $pdo->prepare("INSERT INTO Messages (expediteur_id, destinataire_id, contenu, date_envoi) VALUES (?, ?, ?, NOW())");
        foreach ($concepteur_ids as $cid) {
            $stmt->execute([$user_id, $cid, $message]);
        }
    }
    header("Location: messagerie.php");
    exit;
}

// Récupération des messages avec photo de profil
$messages = [];
if (!empty($concepteur_ids)) {
    $placeholders = implode(',', array_fill(0, count($concepteur_ids), '?'));
    $sql = "
        SELECT m.id_message, m.contenu, m.date_envoi, m.expediteur_id, u.prenom, u.nom, u.photo_profil
        FROM Messages m
        JOIN Utilisateurs u ON u.id_utilisateur = m.expediteur_id
        WHERE (m.expediteur_id = ? AND m.destinataire_id IN ($placeholders))
           OR (m.expediteur_id IN ($placeholders) AND m.destinataire_id = ?)
        ORDER BY m.date_envoi ASC
    ";
    $params = array_merge([$user_id], $concepteur_ids, $concepteur_ids, [$user_id]);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Messagerie</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="styles/style.css" />
  <style>
    body, html {
      margin: 0;
      padding: 0;
      height: 100dvh;
      overflow: hidden;
      font-family: 'Poppins', sans-serif;
      background-color: #f9fafb;
    }

    main.main-content {
      height: 100dvh;
      display: flex;
      flex-direction: column;
      padding-left: 85px;
      transition: padding-left 0.4s ease;
    }

    .sidebar:hover ~ main.main-content {
      padding-left: 260px;
    }

    .sidebar:hover ~ main.main-content .message-form-fixed {
      left: 260px;
    }

    h1 {
      padding: 1.5rem 10% 0.5rem;
      font-size: 1.8rem;
      color: #161a2d;
    }

    .info-banner {
      background-color: #e8edff;
      color: #222b5f;
      padding: 12px 20px;
      border-radius: 12px;
      margin: 0 10% 20px 10%;
      font-size: 0.95rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
      position: relative;
    }

    .info-banner .close-btn {
      position: absolute;
      top: 8px;
      right: 12px;
      cursor: pointer;
      font-size: 1rem;
      color: #4f52ba;
      background: transparent;
      border: none;
    }

    .message-container {
      flex-grow: 1;
      overflow-y: auto;
      padding: 0 10% 140px;
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .message-wrapper {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      max-width: 80%;
    }

    .message-wrapper.recu {
      flex-direction: row;
      align-self: flex-start;
    }

    .message-wrapper.envoye {
      flex-direction: row-reverse;
      align-self: flex-end;
    }

    .avatar {
      flex-shrink: 0;
    }

    .avatar img,
    .avatar .avatar-text {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      object-fit: cover;
      font-size: 0.8rem;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      background-color: #4f52ba;
      color: white;
      border: 2px solid white;
      text-transform: uppercase;
    }

    .message {
      padding: 12px 16px;
      border-radius: 18px;
      max-width: 100%;
      word-wrap: break-word;
      font-size: 0.95rem;
      line-height: 1.4;
      position: relative;
    }

    .message.envoye {
      background-color: #4f52ba;
      color: white;
      border-bottom-right-radius: 4px;
    }

    .message.recu {
      background-color: #f1f1f1;
      color: #111;
      border-bottom-left-radius: 4px;
    }

    .message small {
      display: block;
      font-size: 0.75rem;
      margin-top: 6px;
      opacity: 0.6;
      text-align: right;
    }

    .message-form-fixed {
      position: fixed;
      bottom: 0;
      left: 85px;
      right: 0;
      background: #fff;
      padding: 14px 10%;
      display: flex;
      gap: 12px;
      box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.06);
      z-index: 999;
      transition: left 0.4s ease;
    }

    .message-form-fixed textarea {
      flex-grow: 1;
      padding: 14px;
      min-height: 50px;
      border-radius: 10px;
      resize: none;
      border: 1px solid #ccc;
      font-size: 1rem;
      font-family: inherit;
    }

    .message-form-fixed button {
      background-color: #4f52ba;
      color: white;
      border: none;
      border-radius: 10px;
      padding: 14px 22px;
      font-size: 1rem;
      cursor: pointer;
      font-weight: 500;
      transition: background-color 0.3s ease;
    }

    .message-form-fixed button:hover {
      background-color: #3b3e99;
    }

    @media (max-width: 600px) {
      .message-form-fixed {
        padding: 10px 5%;
        gap: 8px;
      }

      .message-form-fixed textarea {
        font-size: 0.95rem;
        padding: 12px;
      }

      .message-form-fixed button {
        padding: 0;
        width: 44px;
        height: 44px;
        font-size: 0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .message-form-fixed button::before {
        content: "send";
        font-family: 'Material Symbols Outlined';
        font-size: 22px;
        color: white;
      }
    }
  </style>
</head>
<body>
<?php require_once 'includes/sidebar.php'; ?>

<main class="main-content">
  <h1>Messagerie</h1>
  <div class="info-banner" id="infoBanner">
    <span class="close-btn" onclick="document.getElementById('infoBanner').style.display='none'">✖</span>
    💬 Ceci est une <strong>messagerie interne en temps réel</strong> pour discuter directement avec moi.
  </div>

  <div class="message-container" id="message-container">
    <?php if (empty($messages)): ?>
      <p style="color: #777; padding: 1rem;">Aucun message pour l’instant. Envoyez un premier message !</p>
    <?php else: ?>
      <?php foreach ($messages as $msg): ?>
        <div class="message-wrapper <?= $msg['expediteur_id'] == $user_id ? 'envoye' : 'recu' ?>">
          <div class="avatar">
            <?php
              $initiales = strtoupper(substr($msg['prenom'], 0, 1) . substr($msg['nom'], 0, 1));
              if (!empty($msg['photo_profil']) && $msg['photo_profil'] !== 'default.png'):
                  $pp_path = 'uploads/profils/' . htmlspecialchars(basename($msg['photo_profil']));
            ?>
              <img src="<?= $pp_path ?>" alt="Profil">
            <?php else: ?>
              <div class="avatar-text"><?= $initiales ?></div>
            <?php endif; ?>
          </div>
          <div class="message <?= $msg['expediteur_id'] == $user_id ? 'envoye' : 'recu' ?>">
            <strong><?= htmlspecialchars($msg['prenom']) ?>:</strong><br>
            <?= nl2br(htmlspecialchars($msg['contenu'])) ?>
            <small><?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?></small>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <form class="message-form-fixed" method="POST">
    <textarea name="message" placeholder="Écrivez un message..." required></textarea>
    <button type="submit">Envoyer</button>
  </form>
</main>

<script>
  const container = document.getElementById('message-container');

  async function fetchMessages() {
    try {
      const response = await fetch('fetch_messages.php');
      const html = await response.text();
      const isAtBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 20;
      container.innerHTML = html;
      if (isAtBottom) {
        container.scrollTop = container.scrollHeight;
      }
    } catch (err) {
      console.error("Erreur chargement messages :", err);
    }
  }

  setInterval(fetchMessages, 3000);
  fetchMessages();

  window.addEventListener('DOMContentLoaded', () => {
    container.scrollTop = container.scrollHeight;
  });

  const textarea = document.querySelector('.message-form-fixed textarea');
  const form = document.querySelector('.message-form-fixed');

  const isMobile = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
  if (!isMobile) {
    textarea.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        form.requestSubmit();
      }
    });
  }
</script>
</body>
</html>
