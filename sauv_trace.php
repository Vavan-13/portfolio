<?php
ob_start();
session_start();
require_once __DIR__ . '/includes/bdd_connect.php';
require_once __DIR__ . '/includes/sidebar.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("ID de trace invalide.");

$id_trace = (int) $_GET['id'];
$stmt = $pdo->prepare("SELECT T.*, TT.nom_type FROM Traces T JOIN Types_de_traces TT ON T.type_trace_id = TT.id_type WHERE id_trace = ?");
$stmt->execute([$id_trace]);
$trace = $stmt->fetch();

if (!$trace) die("Trace non trouvée.");

function getCategorieIcon($categorie) {
    $map = [
        'Infographie'        => '🖼️',
        'Graphisme'          => '🎨',
        'Développement Web'  => '💻',
        'Vidéo'              => '🎥',
        'Photographie'       => '📷',
        'UI/UX Design'       => '🧩',
        'Projet tutoré'      => '🧠',
        'Communication'      => '🗣️',
        'Réseaux sociaux'    => '🌐'
    ];
    return $map[$categorie] ?? '📁';
}

$canViewComments = false;
$canPostComment = false;

if (isset($_SESSION['role'], $_SESSION['id'])) {
    $role = $_SESSION['role'];
    $user_id = $_SESSION['id'];

    $stmt = $pdo->prepare("SELECT statut_validation FROM Utilisateurs WHERE id_utilisateur = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($role === 'concepteur' || ($role === 'evaluateur' && (int)($user['statut_validation'] ?? 0) === 1)) {
        $canViewComments = $canPostComment = true;
    }
}

$stmt = $pdo->prepare("SELECT * FROM Fichiers_traces WHERE id_trace = ?");
$stmt->execute([$id_trace]);
$fichiers = $stmt->fetchAll();

$commentaires = [];
if ($canViewComments) {
    $stmt = $pdo->prepare("SELECT C.*, U.prenom, U.nom, U.photo_profil FROM Commentaires C JOIN Utilisateurs U ON C.id_utilisateur = U.id_utilisateur WHERE id_trace = ? ORDER BY date_commentaire ASC");
    $stmt->execute([$id_trace]);
    $commentaires = $stmt->fetchAll();
}

$edit_id = null;
$edit_content = '';
if ($canPostComment && isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    foreach ($commentaires as $c) {
        if ($c['id_commentaire'] == $edit_id && $c['id_utilisateur'] == $_SESSION['id']) {
            $edit_content = $c['commentaire'];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($trace['titre']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
   <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="styles/style.css" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      background: #f5f7fb;
    }

    main {
      padding-left: 85px;
      transition: padding-left 0.4s ease;
    }

    .sidebar:hover ~ main {
      padding-left: 260px;
    }

    .container {
      max-width: 900px;
      margin: 40px auto;
      background: #fff;
      border-radius: 16px;
      padding: 32px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }

    .trace-header {
      margin-bottom: 24px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .trace-meta {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 0.9rem;
      color: #344054;
    }

    .back-link {
      text-decoration: none;
      color: #3b82f6;
      font-weight: 500;
      transition: color 0.2s;
    }

    .back-link:hover {
      color: #1d4ed8;
    }

    .trace-subtitle {
      font-weight: 500;
      color: #4b5563;
      font-size: 0.95rem;
    }

    .trace-top {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      flex-wrap: wrap;
    }

    .trace-top h1 {
      font-size: 1.8rem;
      margin: 0;
      font-weight: 600;
      color: #1e293b;
    }

    .trace-top time {
      font-size: 1rem;
      color: #475569;
    }

    .trace-infos {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 16px;
      margin: 24px 0 32px;
    }

    .info-box {
      background: #f1f3f9;
      border-radius: 12px;
      padding: 14px 20px;
      text-align: center;
      font-weight: 500;
    }

    .info-box.full {
      grid-column: span 2;
    }

    .media {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-top: 24px;
    }

    .media-card {
      overflow: hidden;
      border-radius: 14px;
      position: relative;
      cursor: pointer;
      transition: transform 0.3s ease;
      background: #000;
    }

    .media-card:hover {
      transform: scale(1.02);
    }

    .media-card img,
    .media-card video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .comment {
      border: 2px solid #dcddea;
      padding: 14px 18px;
      border-radius: 12px;
      margin-bottom: 18px;
      background: #fff;
      position: relative;
    }

    .comment-header {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .avatar-text {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      display: inline-flex;
      justify-content: center;
      align-items: center;
      font-weight: bold;
      margin-right: 12px;
      background: #4f52ba;
      color: white;
      font-size: 0.9rem;
    }

    .avatar-img {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      object-fit: cover;
      object-position: center;
      display: block;
      margin-right: 12px;
    }

    .comment p { margin: 8px 0; }
    .comment small { color: #777; font-size: 0.8rem; }

    .comment-actions {
      position: absolute;
      top: 12px;
      right: 12px;
      display: flex;
      gap: 8px;
    }

    .comment-actions a {
      font-size: 0.75rem;
      padding: 4px 8px;
      background: #eee;
      border-radius: 6px;
      text-decoration: none;
    }

    textarea {
      width: 100%;
      padding: 12px;
      border-radius: 10px;
      margin-top: 10px;
      border: 1px solid #ccc;
    }

    button {
      margin-top: 10px;
      background: #4f52ba;
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
    }

    @media screen and (max-width: 768px) {
      main {
        padding-left: 85px !important;
      }

      .container {
        margin: 20px;
        padding: 20px;
      }

      .trace-top {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
      }

      .trace-infos {
        grid-template-columns: 1fr;
      }

      .info-box.full {
        grid-column: span 1 !important;
      }

      .comment-actions {
        position: static;
        margin-top: 8px;
        justify-content: flex-end;
      }
    }
  </style>
</head>
<body>
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main>
  <div class="container">

    <div class="trace-header">
      <div class="trace-meta">
        <a href="javascript:history.back()" class="back-link">← Retour</a>
        <span class="trace-subtitle"><?= getCategorieIcon($trace['nom_type']) . ' ' . htmlspecialchars($trace['nom_type']) ?></span>
      </div>
      <div class="trace-top">
        <h1><?= htmlspecialchars($trace['titre']) ?></h1>
        <time><?= date("d/m/Y", strtotime($trace['date_ajout'])) ?></time>
      </div>
    </div>

    <div class="trace-infos">
      <div class="info-box">Année de BUT : <?= htmlspecialchars($trace['annee_creation_BUT']) ?></div>
      <div class="info-box">Compétence : <?= htmlspecialchars($trace['competence_BUT']) ?></div>
      <div class="info-box full"><strong>Apprentissages critiques :</strong> <?= htmlspecialchars($trace['apprentissage_critique']) ?></div>
    </div>

    <h2>Argumentaire</h2>
    <p><?= nl2br(htmlspecialchars($trace['argumentaire_5W'] ?? '-')) ?></p>

    <?php if (!empty($fichiers)): ?>
      <h2>Média</h2>
      <div class="media">
        <?php foreach ($fichiers as $f): ?>
          <div class="media-card">
            <a href="<?= htmlspecialchars($f['fichier_url']) ?>" class="glightbox" data-gallery="medias" <?= $f['type_fichier'] === 'video' ? 'data-type="video"' : '' ?>>
              <img src="<?= $f['type_fichier'] === 'image' ? htmlspecialchars($f['fichier_url']) : '/assets/img/video-placeholder.jpg' ?>" alt="Media">
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($canViewComments): ?>
      <h2>Commentaires</h2>
      <?php foreach ($commentaires as $comm): ?>
        <div class="comment">
          <div class="comment-header">
            <?php if (!empty($comm['photo_profil']) && $comm['photo_profil'] !== 'default.png'): ?>
              <img src="/uploads/profils/<?= htmlspecialchars($comm['photo_profil']) ?>" alt="Photo de profil" class="avatar-img">
            <?php else: ?>
              <div class="avatar-text"><?= strtoupper($comm['prenom'][0] . $comm['nom'][0]) ?></div>
            <?php endif; ?>
            <strong><?= htmlspecialchars($comm['prenom'] . ' ' . $comm['nom']) ?></strong>
          </div>
          <div class="comment-actions">
            <?php if ($_SESSION['id'] == $comm['id_utilisateur']): ?>
              <a href="?id=<?= $id_trace ?>&edit=<?= $comm['id_commentaire'] ?>">✏️</a>
            <?php endif; ?>
            <?php if ($_SESSION['role'] === 'concepteur'): ?>
              <a href="?id=<?= $id_trace ?>&delete=<?= $comm['id_commentaire'] ?>" onclick="return confirm('Supprimer ce commentaire ?')">🗑️</a>
            <?php endif; ?>
          </div>
          <p><?= nl2br(htmlspecialchars($comm['commentaire'])) ?><?= $comm['modifie'] ? ' <em>(modifié)</em>' : '' ?></p>
          <small><?= date('d/m/Y H:i', strtotime($comm['date_commentaire'])) ?></small>
        </div>
      <?php endforeach; ?>

      <?php if ($canPostComment): ?>
        <form method="POST">
          <textarea name="contenu" rows="3" placeholder="Ajouter un commentaire..." required><?= htmlspecialchars($edit_content) ?></textarea>
          <?php if ($edit_id): ?>
            <input type="hidden" name="id_commentaire" value="<?= $edit_id ?>">
          <?php endif; ?>
          <button type="submit"><?= $edit_id ? 'Mettre à jour' : 'Publier' ?></button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
  const lightbox = GLightbox({
    selector: '.glightbox',
    loop: true,
    touchNavigation: true,
    autoplayVideos: true
  });
</script>
</body>
</html>
<?php ob_end_flush(); ?>
