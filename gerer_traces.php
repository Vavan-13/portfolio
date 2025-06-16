<?php 
session_start();
require_once __DIR__ . '/includes/init.php';
require_once $basePath . 'includes/bdd_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- AC LIST ---
$ac_list = [
  'COMPRENDRE' => [
    'AC11.01' => 'Présenter une organisation',
    'AC11.02' => 'Évaluer un site',
    'AC11.03' => 'Produire des analyses statistiques',
    'AC11.04' => 'Analyser des formes médiatiques',
    'AC11.05' => 'Identifier les cibles',
    'AC11.06' => 'Réaliser des entretiens utilisateurs'
  ],
  'CONCEVOIR' => [
    'AC12.01' => 'Concevoir un produit',
    'AC12.02' => 'Construire la valeur d’un produit',
    'AC12.03' => 'Recommandation marketing',
    'AC12.04' => 'Stratégie de communication'
  ],
  'EXPRIMER' => [
    'AC13.01' => 'Écrire pour les médias numériques',
    'AC13.02' => 'Pistes graphiques',
    'AC13.03' => 'Composer et retoucher des visuels',
    'AC13.04' => 'Tourner et monter une vidéo',
    'AC13.05' => 'Designer une interface',
    'AC13.06' => 'Optimiser les médias'
  ],
  'DÉVELOPPER' => [
    'AC14.01' => 'Environnement de développement',
    'AC14.02' => 'Pages Web fluides',
    'AC14.03' => 'Générer à partir de données',
    'AC14.04' => 'Mettre en ligne une app',
    'AC14.05' => 'Modéliser des données',
    'AC14.06' => 'Déployer avec CMS/MVC'
  ],
  'ENTREPRENDRE' => [
    'AC15.01' => 'Gérer un projet',
    'AC15.02' => 'Budgétiser un projet',
    'AC15.03' => 'Découvrir des écosystèmes',
    'AC15.04' => 'Analyser un produit innovant',
    'AC15.05' => 'Présence en ligne pro',
    'AC15.06' => 'Interagir en organisation',
    'AC15.07' => 'Message professionnel'
  ]
];

// --- MESSAGE DE CONFIRMATION VIA URL ---
$message = '';
if (isset($_GET['message']) && $_GET['message'] === 'deleted') {
    $message = "🗑️ Trace supprimée avec succès.";
}

// --- SUPPRESSION TRACE ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    try {
        $pdo->beginTransaction();

// Supprimer les fichiers médias
$stmt = $pdo->prepare("SELECT fichier_url FROM Fichiers_traces WHERE id_trace = ?");
$stmt->execute([$id]);
$fichiers = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($fichiers as $fichier) {
    $filePath = __DIR__ . '/' . $fichier;
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Supprimer la miniature si présente
$stmt = $pdo->prepare("SELECT miniature_url FROM Traces WHERE id_trace = ?");
$stmt->execute([$id]);
$miniature = $stmt->fetchColumn();
if ($miniature && file_exists(__DIR__ . '/' . $miniature)) {
    unlink(__DIR__ . '/' . $miniature);
}

// Supprimer les entrées en base
$pdo->prepare("DELETE FROM Fichiers_traces WHERE id_trace = ?")->execute([$id]);
$pdo->prepare("DELETE FROM Traces WHERE id_trace = ?")->execute([$id]);

$pdo->commit();

// Supprimer le dossier entier s’il existe
$traceFolder = __DIR__ . "/uploads/traces/trace_" . $id;
if (is_dir($traceFolder)) {
    array_map('unlink', glob("$traceFolder/*"));
    rmdir($traceFolder);
}

header("Location: gerer_traces.php?message=deleted");
exit;

        header("Location: gerer_traces.php?message=deleted");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = "❌ Erreur suppression : " . $e->getMessage();
    }
}

// --- MISE À JOUR TRACE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_trace'])) {
    $id = (int) $_POST['id_trace'];
    $titre = trim($_POST['titre']);
    $ac_array = $_POST['apprentissage_critique'] ?? [];
    $apprentissage = implode(',', array_map('trim', $ac_array));
    $argumentaire = trim($_POST['argumentaire_5W']);
    $visibilite = $_POST['visibilite'];
    $categorie = $_POST['categorie'];
    $annee_but = $_POST['annee_but'];
    $competence = $_POST['competence'];
    $partage_public = isset($_POST['partage_public']) ? 1 : 0;

    try {
        // Met à jour les données de la trace
        $stmt = $pdo->prepare("UPDATE Traces SET titre=?, apprentissage_critique=?, argumentaire_5W=?, visibilite=?, partage_public=?, type_trace_id=?, annee_creation_BUT=?, competence_BUT=? WHERE id_trace=?");
        $stmt->execute([$titre, $apprentissage, $argumentaire, $visibilite, $partage_public, $categorie, $annee_but, $competence, $id]);

        // Miniature : suppression si demandé
if (!empty($_POST['delete_miniature'])) {
    $stmt = $pdo->prepare("SELECT miniature_url FROM Traces WHERE id_trace = ?");
    $stmt->execute([$id]);
    $oldMini = $stmt->fetchColumn();
    if ($oldMini && file_exists(__DIR__ . '/' . $oldMini)) {
        unlink(__DIR__ . '/' . $oldMini);
    }
    $stmt = $pdo->prepare("UPDATE Traces SET miniature_url = NULL WHERE id_trace = ?");
    $stmt->execute([$id]);
}


// Miniature : remplacement si nouveau fichier
elseif (!empty($_FILES['miniature']['tmp_name']) && is_uploaded_file($_FILES['miniature']['tmp_name'])) {
    $miniaturePath = "uploads/traces/trace_{$id}/miniature_" . time() . ".jpg";

    if (!is_dir(dirname($miniaturePath))) {
        mkdir(dirname($miniaturePath), 0777, true);
    }

    move_uploaded_file($_FILES['miniature']['tmp_name'], __DIR__ . '/' . $miniaturePath);

    $stmt = $pdo->prepare("UPDATE Traces SET miniature_url = ? WHERE id_trace = ?");
    $stmt->execute([$miniaturePath, $id]);
}



        // Gestion des remplacements de médias existants
        // Gestion des remplacements de médias existants
if (!empty($_FILES['replace_media']['name'])) {
    foreach ($_FILES['replace_media']['name'] as $index => $fileData) {
        if (!empty($fileData['file'])) {
            $tmp = $_FILES['replace_media']['tmp_name'][$index]['file'];
            $fichierId = $_POST['replace_media'][$index]['id_fichier'];
            $originalName = $_FILES['replace_media']['name'][$index]['file'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $type = in_array($ext, ['mp4', 'webm', 'mov']) ? 'video' : 'image';

            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'webm'])) {
                continue;
            }

            // Récupère l'ancien fichier
            $stmtOld = $pdo->prepare("SELECT fichier_url FROM Fichiers_traces WHERE id_fichier = ?");
            $stmtOld->execute([$fichierId]);
            $oldFilePath = $stmtOld->fetchColumn();

            $uploadDir = "uploads/traces/trace_{$id}/";
            if (!is_dir(__DIR__ . '/' . $uploadDir)) {
                mkdir(__DIR__ . '/' . $uploadDir, 0755, true);
            }

            // Nouveau nom unique basé sur horodatage
            $timestamp = time();
            $uniqueSuffix = bin2hex(random_bytes(4)); // ex: a9f23bd1
            $newFilename = "trace_{$id}_{$uniqueSuffix}_{$timestamp}." . $ext;
            $uploadPath = $uploadDir . $newFilename;

            if (move_uploaded_file($tmp, __DIR__ . '/' . $uploadPath)) {
                // Supprimer l'ancien fichier
                if ($oldFilePath && file_exists(__DIR__ . '/' . $oldFilePath)) {
                    unlink(__DIR__ . '/' . $oldFilePath);
                }

                // Mise à jour base de données
                $stmtUpdate = $pdo->prepare("UPDATE Fichiers_traces SET fichier_url = ?, type_fichier = ? WHERE id_fichier = ?");
                $stmtUpdate->execute([$uploadPath, $type, $fichierId]);
            }
        }
    }
}


        // Gestion de l'ajout de nouveaux médias
        if (!empty($_FILES['nouveaux_fichiers']['tmp_name'][0])) {
            $uploadDir = "uploads/traces/trace_" . $id . "/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM Fichiers_traces WHERE id_trace = ?");
            $stmtCount->execute([$id]);
            $startIndex = (int)$stmtCount->fetchColumn();

            foreach ($_FILES['nouveaux_fichiers']['tmp_name'] as $i => $tmpName) {
                if (!empty($tmpName)) {
                    $originalName = $_FILES['nouveaux_fichiers']['name'][$i];
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'webm'])) {
                        continue;
                    }

                    $safeName = "trace_{$id}_" . ($startIndex + $i + 1) . '.' . $ext;
                    $uploadPath = $uploadDir . $safeName;

                    if (move_uploaded_file($tmpName, __DIR__ . '/' . $uploadPath)) {
                        $type = in_array($ext, ['mp4', 'mov', 'webm']) ? 'video' : 'image';
                        $pdo->prepare("INSERT INTO Fichiers_traces (id_trace, fichier_url, type_fichier) VALUES (?, ?, ?)")
                            ->execute([$id, $uploadPath, $type]);
                    }
                }
            }
        }

        $message = "✅ Trace mise à jour.";
    } catch (PDOException $e) {
        $message = "❌ Erreur : " . $e->getMessage();
    }
}


// --- RÉCUP DONNÉES ---
$traces = $pdo->query("SELECT * FROM Traces ORDER BY date_ajout DESC")->fetchAll(PDO::FETCH_ASSOC);
$types = $pdo->query("SELECT * FROM Types_de_traces")->fetchAll(PDO::FETCH_ASSOC);

// ✅ Sidebar affichée *après* les headers
require_once $basePath . 'includes/sidebar.php';
?>



<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gérer mes traces</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="styles/style.css" />
  <style>
    /* ==================== STRUCTURE GLOBALE ==================== */
body {
  font-family: 'Segoe UI', sans-serif;
  background: #f2f5fc;
  margin: 0;
  padding: 0;
}

/* Centrage du contenu via le conteneur principal */
main {
  padding-left: 85px;
  transition: padding-left 0.4s ease;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.sidebar:hover ~ main {
  padding-left: 260px;
}

/* ==================== TITRE & RECHERCHE ==================== */
h1 {
  text-align: center;
  color: #4f52ba;
  margin: 40px 0 30px;
}

.search-input {
  max-width: 400px;
  width: 100%;
  padding: 10px;
  font-size: 1rem;
  border-radius: 8px;
  border: 1px solid #ccc;
  margin-bottom: 30px;
}

/* ==================== CARTE DE TRACE ==================== */
.trace-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 30px;
  max-width: 1200px;
  width: 100%;
  margin-bottom: 60px;
  padding: 0 20px;
}

.trace-card {
  background: white;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
  width: 100%;
  max-width: 800px;
}

/* ==================== FORMULAIRES ==================== */
label {
  display: block;
  margin-top: 12px;
  font-weight: 600;
}

input, select, textarea {
  width: 100%;
  padding: 10px;
  margin-top: 6px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 1rem;
}

textarea {
  resize: vertical;
  min-height: 100px;
}

button {
  background: #4f52ba;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 8px;
  margin-top: 20px;
  cursor: pointer;
  font-size: 1rem;
}

button:hover {
  background: #3b3ea3;
}

.delete-btn {
  background: #f44336;
  text-decoration: none;
  color: white;
  padding: 10px 16px;
  border-radius: 6px;
  margin-top: 10px;
  display: inline-block;
}

/* ==================== MÉDIA ==================== */
.media-item {
  background: #f4f5fa;
  border-radius: 10px;
  padding: 12px;
  margin-top: 10px;
}

.media-item img,
.media-item video {
  max-width: 100%;
  border-radius: 8px;
  margin-top: 10px;
}

/* ==================== CHOICES.JS ==================== */
.choices__item--selectable {
  background: #4f52ba !important;
  color: #fff !important;
  border-radius: 20px !important;
  padding: 6px 12px !important;
  font-weight: 500;
}

.choices__item--selectable .choices__button {
  background: none !important;
  border: none;
  color: white;
  font-size: 1rem;
  padding-left: 6px;
  cursor: pointer;
  transition: opacity 0.2s ease-in-out;
}

.choices__item--selectable .choices__button:hover {
  opacity: 0.7;
}

/* ==================== CHECKBOX STYLISÉ ==================== */
.styled-checkbox {
  position: relative;
  padding-left: 36px;
  cursor: pointer;
  font-size: 1rem;
  user-select: none;
  display: inline-block;
  color: #333;
}

.styled-checkbox input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

.styled-checkbox .checkmark {
  position: absolute;
  top: 0;
  left: 0;
  height: 20px;
  width: 20px;
  background-color: #eee;
  border-radius: 6px;
  border: 1px solid #ccc;
  transition: 0.3s;
}

.styled-checkbox input:checked ~ .checkmark {
  background-color: #4f52ba;
  border-color: #4f52ba;
}

.styled-checkbox .checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

.styled-checkbox input:checked ~ .checkmark:after {
  display: block;
  left: 6px;
  top: 2px;
  width: 5px;
  height: 10px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

/* ==================== RÉPONSIVE ==================== */
@media (max-width: 1024px) {
  .trace-grid {
    grid-template-columns: 1fr;
  }

  .trace-card {
    padding: 20px;
    margin-bottom: 30px;
  }

  input, select, textarea {
    font-size: 0.95rem;
    padding: 8px;
  }

  button, .delete-btn {
    width: 100%;
    margin-left: 0;
    margin-top: 10px;
    display: block;
    text-align: center;
  }
}

@media (max-width: 500px) {
  h1 {
    font-size: 1.5rem;
    padding: 0 10px;
  }

  label {
    font-size: 0.95rem;
  }

  .styled-checkbox {
    font-size: 0.95rem;
  }

  .styled-checkbox .checkmark {
    width: 18px;
    height: 18px;
  }

  .styled-checkbox .checkmark:after {
    left: 5px;
    top: 1px;
    width: 4px;
    height: 8px;
  }
}

.styled-checkbox {
  display: flex;
  align-items: center;
  gap: 10px;
  line-height: 1.2;
}

.styled-checkbox .checkmark {
  flex-shrink: 0;
}




  </style>
</head>
<body>
  <main>
  <h1>🎓 Gestion des Traces</h1>

  <?php if (!empty($message)): ?>
    <div style="background:#d0f0d0; padding:12px; border-left: 5px solid green; border-radius: 8px; margin-bottom: 30px; max-width:800px; margin:auto;">
      <?= $message ?>
    </div>
  <?php endif; ?>
  <input type="text" id="searchInput" class="search-input" placeholder="🔍 Rechercher une trace..." />

<div class="trace-grid">
  <?php foreach ($traces as $trace): ?>
    <?php
      $stmt = $pdo->prepare("SELECT * FROM Fichiers_traces WHERE id_trace = ?");
      $stmt->execute([$trace['id_trace']]);
      $fichiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $ac_selected = !empty($trace['apprentissage_critique']) ? explode(',', $trace['apprentissage_critique']) : [];

    ?>
    <div class="trace-card" data-title="<?= htmlspecialchars(strtolower($trace['titre'])) ?>">

      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id_trace" value="<?= $trace['id_trace'] ?>">
                <p style="font-size: 0.9rem; color: #666; margin-top: 0; margin-bottom: 10px;">
          ID de la trace : <strong><?= $trace['id_trace'] ?></strong>
        </p>

        <label>Titre</label>
        <input type="text" name="titre" value="<?= htmlspecialchars($trace['titre']) ?>">

        <label>Apprentissages Critiques</label>
        <select name="apprentissage_critique[]" multiple>
          <?php foreach ($ac_list as $group => $acs): ?>
            <optgroup label="<?= $group ?>">
              <?php foreach ($acs as $key => $label): ?>
                <option value="<?= $key ?>" <?= in_array($key, $ac_selected) ? 'selected' : '' ?>><?= $key ?> - <?= $label ?></option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>

        <label>Argumentaire</label>
        <textarea name="argumentaire_5W"><?= htmlspecialchars($trace['argumentaire_5W']) ?></textarea>

        <label>Type</label>
        <select name="categorie">
          <?php foreach ($types as $type): ?>
            <option value="<?= $type['id_type'] ?>" <?= $trace['type_trace_id'] == $type['id_type'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($type['nom_type']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label>Année BUT</label>
        <select name="annee_but">
          <?php for ($i = 1; $i <= 3; $i++): ?>
            <option value="<?= $i ?>" <?= $trace['annee_creation_BUT'] == $i ? 'selected' : '' ?>><?= $i ?></option>
          <?php endfor; ?>
        </select>

        <label>Compétence</label>
        <select name="competence" multiple>
          <?php foreach (['Comprendre', 'Concevoir', 'Exprimer', 'Développer', 'Entreprendre'] as $comp): ?>
            <option value="<?= $comp ?>" <?= $trace['competence_BUT'] == $comp ? 'selected' : '' ?>><?= $comp ?></option>
          <?php endforeach; ?>
        </select>

        <label>Visibilité</label>
        <select name="visibilite">
          <option value="portfolio_public" <?= $trace['visibilite'] === 'portfolio_public' ? 'selected' : '' ?>>Public</option>
          <option value="portfolio_academique" <?= $trace['visibilite'] === 'portfolio_academique' ? 'selected' : '' ?>>Académique</option>
          <option value="prive" <?= $trace['visibilite'] === 'prive' ? 'selected' : '' ?>>Privé</option>
        </select>

        
      <div class="styled-checkbox-container" style="<?= $trace['visibilite'] === 'portfolio_academique' ? '' : 'display:none;' ?>">
  <label class="styled-checkbox">
    <input type="checkbox" name="partage_public" value="1" <?= $trace['partage_public'] ? 'checked' : '' ?>>
    <span class="checkmark"></span>
    Afficher aussi dans le portfolio public
  </label>
</div>


        <?php foreach ($fichiers as $index => $fichier): ?>
          <div class="media-item">
            <?php if ($fichier['type_fichier'] === 'image'): ?>
              <img src="<?= htmlspecialchars($fichier['fichier_url']) . '?v=' . time() ?>" alt="Image">
            <?php else: ?>
              <video controls src="<?= htmlspecialchars($fichier['fichier_url']) . '?v=' . time() ?>"></video>
            <?php endif; ?>
            <label>Remplacer le média :</label>
            <input type="file" name="replace_media[<?= $index ?>][file]">
            <input type="hidden" name="replace_media[<?= $index ?>][id_fichier]" value="<?= $fichier['id_fichier'] ?>">
          </div>
        <?php endforeach; ?>

        <div class="media-item">
          <label>Ajouter un nouveau média :</label>
          <input type="file" name="nouveaux_fichiers[]" accept=".jpg,.jpeg,.png,.gif,.mp4,.mov,.webm" multiple>
        </div>

        <?php if (!empty($trace['miniature_url'])): ?>
  <div class="media-item">
    <label>Miniature actuelle :</label><br>
    <img src="<?= htmlspecialchars($trace['miniature_url']) ?>" style="max-width: 200px; margin-top: 10px;"><br>
    <label class="styled-checkbox">
  <input type="checkbox" name="delete_miniature" value="1">
  <span class="checkmark"></span>
  Supprimer la miniature
</label>

  </div>
<?php endif; ?>

<div class="media-item">
  <label>Nouvelle miniature (remplace l’actuelle si envoyée) :</label>
  <input type="file" name="miniature" accept="image/*">
</div>



        <button type="submit" name="update_trace">💾 Enregistrer</button>
        <a class="delete-btn" href="?delete=<?= $trace['id_trace'] ?>" onclick="return confirm('Supprimer cette trace ?')">🗑 Supprimer</a>
      </form>
    </div>
  <?php endforeach; ?>
            </div>
<script>
  const searchInput = document.getElementById('searchInput');
  searchInput.addEventListener('input', function () {
    const searchTerm = this.value.toLowerCase();
    const cards = document.querySelectorAll('.trace-card');
    cards.forEach(card => {
      const title = card.getAttribute('data-title');
      if (title.includes(searchTerm)) {
        card.style.display = '';
      } else {
        card.style.display = 'none';
      }
    });
  });
</script>

  <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
  <script>
  document.querySelectorAll('.visibilite-select').forEach(select => {
    select.addEventListener('change', function () {
      const checkboxDiv = this.closest('form').querySelector('.checkbox-container');
      checkboxDiv.style.display = this.value === 'portfolio_academique' ? 'block' : 'none';
    });
  });
</script>

  <script>
    document.querySelectorAll('select[multiple]').forEach(select => {
      new Choices(select, {
        removeItemButton: true,
        shouldSort: false,
        placeholderValue: 'Sélectionnez...',
        searchPlaceholderValue: 'Rechercher...'
      });
    });
  </script>
  <script>
  document.querySelectorAll('select[name="visibilite"]').forEach(select => {
    select.addEventListener('change', function () {
      const container = this.closest('form').querySelector('.styled-checkbox-container');
      if (container) {
        container.style.display = this.value === 'portfolio_academique' ? 'block' : 'none';
      }
    });
  });
</script>
</main>
</body>
</html>