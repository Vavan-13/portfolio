<?php
require_once __DIR__ . '/includes/init.php';
require_once $basePath . 'includes/bdd_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'concepteur') {
    header('Location: index.php');
    exit;
}

$concepteur_id = $_SESSION['id_utilisateur'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reponse'], $_POST['destinataire_id'])) {
    $reponse = trim($_POST['reponse']);
    $destinataire_id = (int) $_POST['destinataire_id'];
    if ($reponse !== '') {
        $stmt = $pdo->prepare("INSERT INTO Messages (expediteur_id, destinataire_id, contenu, date_envoi, lu) VALUES (?, ?, ?, NOW(), 0)");
        $stmt->execute([$concepteur_id, $destinataire_id, $reponse]);
    }
    header("Location: messagerie_admin.php?uid=$destinataire_id");
    exit;
}

$stmt = $pdo->prepare("SELECT u.*, MAX(m.date_envoi) as last_msg_date,
    SUM(CASE WHEN m.destinataire_id = :cid AND m.lu = 0 THEN 1 ELSE 0 END) AS nb_non_lus
    FROM Utilisateurs u
    JOIN Messages m ON (u.id_utilisateur = m.expediteur_id OR u.id_utilisateur = m.destinataire_id)
    WHERE u.role IN ('visiteur', 'evaluateur')
      AND (m.expediteur_id IN (SELECT id_utilisateur FROM Utilisateurs WHERE role = 'concepteur')
           OR m.destinataire_id IN (SELECT id_utilisateur FROM Utilisateurs WHERE role = 'concepteur'))
      AND u.id_utilisateur != :cid
    GROUP BY u.id_utilisateur
    ORDER BY last_msg_date DESC");
$stmt->execute(['cid' => $concepteur_id]);
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selected_user_id = $_GET['uid'] ?? ($utilisateurs[0]['id_utilisateur'] ?? null);
$messages = [];

if ($selected_user_id) {
    $stmtConv = $pdo->prepare("SELECT m.*, u.prenom, u.nom FROM Messages m
        JOIN Utilisateurs u ON u.id_utilisateur = m.expediteur_id
        WHERE 
          (m.expediteur_id = :uid AND m.destinataire_id IN (
              SELECT id_utilisateur FROM Utilisateurs WHERE role = 'concepteur'
          ))
          OR 
          (m.destinataire_id = :uid AND m.expediteur_id IN (
              SELECT id_utilisateur FROM Utilisateurs WHERE role = 'concepteur'
          ))
        ORDER BY m.date_envoi ASC");
    $stmtConv->execute(['uid' => $selected_user_id]);
    $messages = $stmtConv->fetchAll(PDO::FETCH_ASSOC);

    $updateLu = $pdo->prepare("UPDATE Messages SET lu = 1 
        WHERE expediteur_id = ? AND destinataire_id = ? AND lu = 0");
    $updateLu->execute([$selected_user_id, $concepteur_id]);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Messagerie - Concepteur</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="styles/style.css" />
  <style>
    body, html {
      margin: 0;
      padding: 0;
      height: 100dvh;
      font-family: 'Poppins', sans-serif;
      background-color: #F0F4FF;
      overflow: hidden;
    }
    main {
      display: flex;
      height: 100dvh;
      padding-left: 85px;
      transition: padding-left 0.4s ease;
    }
    .sidebar:hover ~ main {
      padding-left: 260px;
    }
    .user-panel {
      width: 300px;
      background-color: #fff;
      border-right: 1px solid #ddd;
      overflow-y: auto;
    }
    .user-panel ul {
      list-style: none;
      margin: 0;
      padding: 0;
    }
    .user-panel li a {
      display: block;
      padding: 14px 16px;
      text-decoration: none;
      color: #222;
      border-bottom: 1px solid #f0f0f0;
      transition: background-color 0.2s;
    }
    .user-panel li.active a,
    .user-panel li a:hover {
      background-color: #e6ebf9;
      font-weight: 500;
      color: #4f52ba;
    }
    .badge {
      background-color: #e53935;
      color: white;
      font-size: 0.75rem;
      font-weight: bold;
      border-radius: 12px;
      padding: 2px 7px;
      margin-left: 8px;
      vertical-align: middle;
    }
    .conversation {
      flex: 1;
      display: flex;
      flex-direction: column;
      background: #fff;
      padding: 20px;
      overflow: hidden;
    }
    .mobile-nav {
      display: none;
      margin-bottom: 16px;
    }
    .mobile-nav select {
      width: 100%;
      padding: 10px;
      font-size: 1rem;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-family: inherit;
      background-color: #fff;
    }
    .conversation h2 {
      margin-bottom: 16px;
      font-size: 1.4rem;
      color: #4f52ba;
    }
    .messages {
      flex-grow: 1;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 12px;
      padding-right: 10px;
    }
    .message {
      padding: 14px 16px;
      border-radius: 14px;
      max-width: 70%;
      font-size: 0.95rem;
      line-height: 1.5;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      transition: transform 0.2s;
      word-wrap: break-word;
      overflow-wrap: break-word;
      white-space: pre-wrap;
    }
    .message img {
      max-width: 100%;
      max-height: 250px;
      border-radius: 8px;
      object-fit: cover;
      display: block;
    }
    .message.envoye {
      background-color: #4f52ba;
      color: white;
      margin-left: auto;
      text-align: right;
      border-bottom-right-radius: 4px;
    }
    .message.recu {
      background-color: #f2f5ff;
      color: #222;
      margin-right: auto;
      text-align: left;
      border-bottom-left-radius: 4px;
    }
    .message small {
      display: block;
      font-size: 0.75rem;
      opacity: 0.6;
      margin-top: 4px;
    }
    .message-form {
      display: flex;
      gap: 12px;
      border-top: 1px solid #ddd;
      padding-top: 14px;
      margin-top: 16px;
    }
    .message-form textarea {
      flex-grow: 1;
      padding: 12px 14px;
      border-radius: 10px;
      border: 1px solid #ccc;
      font-size: 1rem;
      font-family: inherit;
      outline-color: #4f52ba;
      resize: none;
      min-height: 48px;
    }
    .message-form button {
      background-color: #4f52ba;
      color: white;
      border: none;
      border-radius: 8px;
      padding: 12px 18px;
      font-size: 1rem;
      cursor: pointer;
      font-weight: 500;
    }
    .message-form button:hover {
      background-color: #3a3db5;
    }

    @media (max-width: 768px) {
      .user-panel {
        display: none;
      }
      .mobile-nav {
        display: block;
      }
    }

    @media (max-width: 600px) {
      .message {
        max-width: 100%;
      }

      .message-form {
        gap: 8px;
      }

      .message-form textarea {
        font-size: 0.95rem;
        padding: 10px;
      }

      .message-form button {
        width: 44px;
        height: 44px;
        padding: 0;
        font-size: 0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .message-form button::before {
        content: "send";
        font-family: 'Material Symbols Outlined';
        font-size: 22px;
        color: white;
      }
    }

    .message-wrapper {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  max-width: 80%;
}

.message-wrapper.envoye {
  flex-direction: row-reverse;
  align-self: flex-end;
}

.message-wrapper.recu {
  flex-direction: row;
  align-self: flex-start;
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

  </style>
</head>
<body>

<?php require_once 'includes/sidebar.php'; ?>

<main>
  <div class="user-panel">
    <ul id="user-list">
      <?php foreach ($utilisateurs as $user): ?>
        <li class="<?= $user['id_utilisateur'] == $selected_user_id ? 'active' : '' ?>">
          <a href="messagerie_admin.php?uid=<?= $user['id_utilisateur'] ?>">
            <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
            <?php if (!empty($user['nb_non_lus'])): ?>
              <span class="badge"><?= $user['nb_non_lus'] ?></span>
            <?php endif; ?>
            <br><small><?= htmlspecialchars($user['email']) ?></small>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="conversation">
    <div class="mobile-nav">
      <select onchange="if(this.value) window.location.href=this.value">
        <option disabled selected>Choisir un utilisateur</option>
        <?php foreach ($utilisateurs as $user): ?>
          <option value="messagerie_admin.php?uid=<?= $user['id_utilisateur'] ?>" <?= $user['id_utilisateur'] == $selected_user_id ? 'selected' : '' ?>>
            <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <h2>Conversation</h2>

    <div class="messages" id="messages-container">
      <?php foreach ($messages as $msg): ?>
        <div class="message-wrapper <?= $msg['expediteur_id'] == $concepteur_id ? 'envoye' : 'recu' ?>">
  <div class="avatar">
    <?php
      $initiales = strtoupper(substr($msg['prenom'], 0, 1) . substr($msg['nom'], 0, 1));
      $photo = !empty($msg['photo_profil']) && $msg['photo_profil'] !== 'default.png'
          ? 'uploads/profils/' . htmlspecialchars(basename($msg['photo_profil']))
          : null;
    ?>
    <?php if ($photo && file_exists($photo)): ?>
      <img src="<?= $photo ?>" alt="Profil">
    <?php else: ?>
      <div class="avatar-text"><?= $initiales ?></div>
    <?php endif; ?>
  </div>
  <div class="message <?= $msg['expediteur_id'] == $concepteur_id ? 'envoye' : 'recu' ?>">
    <strong><?= htmlspecialchars($msg['prenom']) ?>:</strong><br>
    <?= nl2br(htmlspecialchars($msg['contenu'])) ?>
    <small><?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?></small>
  </div>
</div>

      <?php endforeach; ?>
    </div>

    <?php if ($selected_user_id): ?>
      <form class="message-form" method="POST" id="messageForm">
        <input type="hidden" name="destinataire_id" value="<?= $selected_user_id ?>">
        <textarea name="reponse" id="reponse" placeholder="Écrivez votre message..." required></textarea>
        <button type="submit">Envoyer</button>
      </form>
    <?php endif; ?>
  </div>
</main>

<script>
  const messagesDiv = document.querySelector('.messages');
  if (messagesDiv) messagesDiv.scrollTop = messagesDiv.scrollHeight;
</script>
<script>
const container = document.getElementById('messages-container');
const uid = <?= json_encode($selected_user_id) ?>;

async function fetchMessages() {
  try {
    const response = await fetch('fetch_messages.php?uid=' + uid);
    const html = await response.text();

    const isAtBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 20;
    container.innerHTML = html;

    if (isAtBottom) {
      container.scrollTop = container.scrollHeight;
    }
  } catch (err) {
    console.error("Erreur chargement messages admin :", err);
  }
}
setInterval(fetchMessages, 3000);
fetchMessages();
</script>

<script>
const userList = document.getElementById('user-list');
async function fetchUserList() {
  try {
    const res = await fetch('fetch_user_list.php?selected=' + uid);
    const html = await res.text();
    userList.innerHTML = html;
  } catch (e) {
    console.error("Erreur chargement liste utilisateurs :", e);
  }
}
setInterval(fetchUserList, 5000);
fetchUserList();
</script>

<script>
const textarea = document.getElementById('reponse');
const form = document.getElementById('messageForm');

textarea.addEventListener('keydown', function(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    form.submit();
  }
});
</script>

</body>
</html>
