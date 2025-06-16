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
        'Infographie'=>'🖼️','Graphisme'=>'🎨','Développement Web'=>'💻',
        'Vidéo'=>'🎥','Photographie'=>'📷','UI/UX Design'=>'🧩',
        'Projet tutoré'=>'🧠','Communication'=>'🗣️','Réseaux sociaux'=>'🌐'
    ];
    return $map[$categorie] ?? '📁';
}

$canViewComments = $canPostComment = false;
if (isset($_SESSION['role'], $_SESSION['id_utilisateur'])) {
    $role = $_SESSION['role'];
    $user_id = $_SESSION['id_utilisateur'];
    $stmt = $pdo->prepare("SELECT statut_validation FROM Utilisateurs WHERE id_utilisateur=?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($role === 'concepteur' || ($role === 'evaluateur' && (int)($user['statut_validation'] ?? 0) === 1)) {
        $canViewComments = $canPostComment = true;
    }
}

$stmt = $pdo->prepare("SELECT * FROM Fichiers_traces WHERE id_trace=?");
$stmt->execute([$id_trace]);
$fichiers = $stmt->fetchAll();

// Suppression de commentaire
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && ($_SESSION['role'] ?? '') === 'concepteur') {
    $delete_id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("SELECT id_commentaire FROM Commentaires WHERE id_commentaire=? AND id_trace=?");
    $stmt->execute([$delete_id, $id_trace]);
    if ($stmt->fetch()) {
        $pdo->prepare("DELETE FROM Commentaires WHERE id_commentaire=?")->execute([$delete_id]);
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=$id_trace&message=suppr#comment-$delete_id");
        exit;
    }
}

// Ajout / édition de commentaire
if ($canPostComment && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contenu'])) {
    $contenu = trim($_POST['contenu']);
    $id_utilisateur = $_SESSION['id_utilisateur'];
    $commentaire_id = $_POST['id_commentaire'] ?? null;
    if ($contenu !== '') {
        if ($commentaire_id) {
            $stmt = $pdo->prepare("UPDATE Commentaires SET commentaire=?, modifie=1 WHERE id_commentaire=? AND id_utilisateur=?");
            $stmt->execute([$contenu, $commentaire_id, $id_utilisateur]);
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=$id_trace&message=modif#comment-$commentaire_id");
        } else {
            $stmt = $pdo->prepare("INSERT INTO Commentaires (id_trace, id_utilisateur, commentaire, date_commentaire) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$id_trace, $id_utilisateur, $contenu]);
            $new_id = $pdo->lastInsertId();
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=$id_trace&message=ajout#comment-$new_id");
        }
        exit;
    }
}

$commentaires = [];
if ($canViewComments) {
    $stmt = $pdo->prepare("SELECT C.*, U.prenom, U.nom, U.photo_profil
        FROM Commentaires C
        JOIN Utilisateurs U ON C.id_utilisateur = U.id_utilisateur
        WHERE id_trace = ? ORDER BY date_commentaire ASC");
    $stmt->execute([$id_trace]);
    $commentaires = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF‑8">
  <title><?=htmlspecialchars($trace['titre'])?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
  <link rel="stylesheet" href="styles/style.css">
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
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

/* ------------------ HEADER ------------------ */
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
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
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
  color: #1e293b;
}

/* ------------------ INFO BOXES ------------------ */
.trace-infos {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin: 24px 0 32px;
  justify-content: space-between;
}

.info-box {
  flex: 1 1 100%;
  background: #f0f4ff;
  border-radius: 14px;
  padding: 16px 20px;
  font-weight: 500;
  text-align: left;
  color: #1e293b;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  transition: background 0.3s ease;

  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
  white-space: normal;
}

.info-box strong {
  display: block;
  margin-bottom: 4px;
  color: #4f52ba;
  font-weight: 600;
  font-size: 0.9rem;
}

.info-box.full {
  flex: 1 1 100%;
}

@media (min-width: 600px) {
  .info-box {
    flex: 1 1 calc(50% - 8px);
  }

  .info-box.full {
    flex: 1 1 100%;
  }
}

@media (min-width: 900px) {
  .info-box {
    flex: 1 1 calc(33% - 11px);
  }

  .info-box.full {
    flex: 1 1 100%;
  }
}

/* ------------------ MEDIA SECTION ------------------ */
.media {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-top: 24px;
}

.media-card {
  aspect-ratio: 16 / 9;
  background-color: #000;
  border-radius: 14px;
  overflow: hidden;
  position: relative;
}

.media-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.video-play-icon {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 2.5rem;
  color: rgba(255, 255, 255, 0.8);
  text-shadow: 0 0 8px rgba(0, 0, 0, 0.6);
  pointer-events: none;
  transition: opacity 0.2s ease;
}

.media-card:hover .video-play-icon {
  opacity: 0;
}

/* ------------------ COMMENTAIRES ------------------ */
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
  background: #4f52ba;
  color: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  font-weight: bold;
}

.avatar-img {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  object-fit: cover;
}

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
  color: #fff;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

/* ------------------ GLOBAL RESPONSIVE ------------------ */
@media (max-width: 768px) {
  main {
    padding-left: 85px !important;
  }

  .container {
    margin: 20px;
    padding: 20px;
  }
}


  </style>
</head>
<body>
<?php require_once __DIR__.'/includes/sidebar.php'; ?>
<main>
  <div class="container">
    <div class="trace-header">
      <div class="trace-meta">
        <a href="mes_realisations.php" class="back-link">← Retour</a>
        <span class="trace-subtitle"><?=getCategorieIcon($trace['nom_type']).' '.htmlspecialchars($trace['nom_type'])?></span>
      </div>
      <div class="trace-top">
        <h1><?=htmlspecialchars($trace['titre'])?></h1>
        <time><?=date("d/m/Y",strtotime($trace['date_ajout']))?></time>
      </div>
    </div>
    <div class="trace-infos">
  <div class="info-box">
    <strong>Année de BUT</strong>
    <?=htmlspecialchars($trace['annee_creation_BUT'])?>
  </div>
  <div class="info-box">
    <strong>Compétence</strong>
    <?=htmlspecialchars($trace['competence_BUT'])?>
  </div>
  <div class="info-box full">
    <strong>Apprentissages critiques</strong>
    <?=htmlspecialchars($trace['apprentissage_critique'])?>
  </div>
</div>


   
    <?php if (!empty($fichiers)): ?>
      <h2>Média</h2>
      <div class="media">
        <?php foreach ($fichiers as $f): ?>
          <div class="media-card" style="position: relative;">
            <?php if ($f['type_fichier'] === 'video'): ?>
              <a href="<?= htmlspecialchars($f['fichier_url']) ?>" class="glightbox" data-gallery="medias" data-type="video" style="display: block; position: relative;">
                <video muted playsinline preload="metadata"
                  style="width: 100%; height: 100%; object-fit: cover; display: block;"
                  onmouseenter="this.play()" onmouseleave="this.pause(); this.currentTime = 0;">
                  <source src="<?= htmlspecialchars($f['fichier_url']) ?>" type="video/mp4">
                  Votre navigateur ne supporte pas la vidéo.
                </video>
                <span class="video-play-icon">▶️</span>
              </a>
            <?php else: ?>
              <a href="<?= htmlspecialchars($f['fichier_url']) ?>" class="glightbox" data-gallery="medias">
                <img src="<?= htmlspecialchars($f['fichier_url']) ?>" alt="Media">
              </a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

     <h2>Argumentaire</h2>
    <p><?=nl2br(htmlspecialchars($trace['argumentaire_5W'] ?? '-'))?></p>


    <?php if ($canViewComments): ?>
      <h2>Commentaires</h2>
      <?php foreach ($commentaires as $comm): ?>
        <div class="comment" id="comment-<?= $comm['id_commentaire'] ?>">
          <div class="comment-header">
            <?php if (!empty($comm['photo_profil']) && $comm['photo_profil']!=='default.png'): ?>
              <img src="/uploads/profils/<?=htmlspecialchars($comm['photo_profil'])?>" class="avatar-img">
            <?php else: ?>
              <div class="avatar-text"><?=strtoupper($comm['prenom'][0].$comm['nom'][0])?></div>
            <?php endif; ?>
            <strong><?=htmlspecialchars($comm['prenom'].' '.$comm['nom'])?></strong>
          </div>
          <div class="comment-actions">
            <?php if ($_SESSION['id_utilisateur'] == $comm['id_utilisateur']): ?>
              <a href="?id=<?= $id_trace ?>&edit=<?= $comm['id_commentaire'] ?>#comment-<?= $comm['id_commentaire'] ?>">✏️</a>
            <?php endif; ?>
            <?php if ($_SESSION['role']==='concepteur'): ?>
              <a href="?id=<?= $id_trace ?>&delete=<?= $comm['id_commentaire'] ?>#comment-<?= $comm['id_commentaire'] ?>" onclick="return confirm('Supprimer ce commentaire ?')">🗑️</a>
            <?php endif; ?>
          </div>
          <?php if (isset($_GET['edit']) && $_GET['edit']==$comm['id_commentaire']): ?>
            <form method="POST">
              <textarea name="contenu" rows="3" required><?=htmlspecialchars($comm['commentaire'])?></textarea>
              <input type="hidden" name="id_commentaire" value="<?=$comm['id_commentaire']?>">
              <button type="submit">Mettre à jour</button>
            </form>
          <?php else: ?>
            <p><?=nl2br(htmlspecialchars($comm['commentaire']))?><?= $comm['modifie']? ' <em>(modifié)</em>':''?></p>
          <?php endif; ?>
          <small><?=date('d/m/Y H:i',strtotime($comm['date_commentaire']))?></small>
        </div>
      <?php endforeach; ?>
      <form method="POST">
        <textarea name="contenu" rows="3" placeholder="Ajouter un commentaire..." required></textarea>
        <button type="submit">Publier</button>
      </form>
    <?php endif; ?>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
  const lightbox = GLightbox({ selector: '.glightbox', loop: true });
  document.addEventListener("DOMContentLoaded", () => {
    const hash = location.hash;
    const message = "<?= $_GET['message'] ?? '' ?>";
    const el = hash.startsWith("#comment-") ? document.querySelector(hash) : null;
    if (el) {
      el.scrollIntoView({ behavior: "smooth", block: "center" });
      let bg = "#fef3c7";
      if (message === "suppr") bg = "#fee2e2";
      else if (message === "ajout") bg = "#d1fae5";
      el.style.transition = "background 0.5s";
      el.style.background = bg;
      setTimeout(() => el.style.background = "", 1500);
      if (location.search.includes("edit")) {
        const textarea = el.querySelector("textarea");
        if (textarea) textarea.focus();
      }
    }
  });
</script>
</body>
</html>
<?php ob_end_flush(); ?>