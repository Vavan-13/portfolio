<?php
require_once __DIR__ . '/includes/init.php';
require_once $basePath . 'includes/bdd_connect.php';
require_once $basePath . 'includes/sidebar.php';


$type_filter = $_GET['type'] ?? '';
$annee_filter = $_GET['annee'] ?? '';
$ac_filter = $_GET['ac'] ?? '';
$comp_filter = $_GET['comp'] ?? '';
$order = $_GET['order'] ?? 'date_ajout';
$visibilite_filter = $_GET['visibilite'] ?? '';

setlocale(LC_TIME, 'fr_FR.UTF-8');



$sql = "
  SELECT T.id_trace, T.titre, T.annee_creation_BUT, T.competence_BUT, T.argumentaire_5W, T.date_ajout, 
       T.apprentissage_critique, T.miniature_url, TT.nom_type
  FROM Traces T
  JOIN Types_de_traces TT ON T.type_trace_id = TT.id_type
  WHERE 1=1
";

$params = []; // ✅ AJOUTÉ pour éviter l'erreur si aucun filtre

$userId = $_SESSION['id_utilisateur'] ?? null;
$userRole = $_SESSION['role'] ?? 'visiteur';
$statut_validation = $_SESSION['statut_validation'] ?? 0;

// Gestion des règles de visibilité selon le rôle
$visibilite_filter = $_GET['visibilite'] ?? '';

if ($userRole === 'concepteur') {
    if (in_array($visibilite_filter, ['portfolio_public', 'portfolio_academique', 'prive'])) {
        if ($visibilite_filter === 'portfolio_public') {
    $sql .= " AND (T.visibilite = 'portfolio_public' OR (T.visibilite = 'portfolio_academique' AND T.partage_public = 1))";
} elseif ($visibilite_filter === 'portfolio_academique') {
    $sql .= " AND T.visibilite = 'portfolio_academique'";
} elseif ($visibilite_filter === 'prive') {
    $sql .= " AND T.visibilite = 'prive'";
}

    }
} elseif ($userRole === 'evaluateur' && $statut_validation === 1) {
    if (in_array($visibilite_filter, ['portfolio_public', 'portfolio_academique'])) {
        $sql .= " AND T.visibilite = ?";
        $params[] = $visibilite_filter;
    } else {
        $sql .= " AND (T.visibilite = 'portfolio_public' OR T.visibilite = 'portfolio_academique')";
    }
} else {
     $sql .= " AND (T.visibilite = 'portfolio_public' OR (T.visibilite = 'portfolio_academique' AND T.partage_public = 1))";
}


// Filtres
if ($type_filter) {
    $sql .= " AND T.type_trace_id = ?";
    $params[] = $type_filter;
}
if ($annee_filter) {
    $sql .= " AND T.annee_creation_BUT = ?";
    $params[] = $annee_filter;
}
if ($ac_filter) {
    $sql .= " AND T.apprentissage_critique = ?";
    $params[] = $ac_filter;
}
if ($comp_filter) {
    $sql .= " AND T.competence_BUT = ?";
    $params[] = $comp_filter;
}

// Sécurisation du tri
$validOrderFields = ['date_ajout', 'date_ajout_asc', 'annee_creation_BUT', 'titre'];

switch ($order) {
    case 'date_ajout_asc':
        $sql .= " ORDER BY T.date_ajout ASC";
        break;
    case 'titre':
        $sql .= " ORDER BY T.titre ASC";
        break;
    case 'annee_creation_BUT':
        $sql .= " ORDER BY T.annee_creation_BUT DESC";
        break;
    default:
        $sql .= " ORDER BY T.date_ajout DESC";
        break;
}



// Exécution
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$traces = $stmt->fetchAll();

// Récupération des fichiers
$fichiersParTrace = [];
if ($traces) {
    $ids = array_column($traces, 'id_trace');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("
        SELECT id_trace, fichier_url, type_fichier
        FROM Fichiers_traces
        WHERE id_trace IN ($placeholders)
    ");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $fichier) {
        $fichiersParTrace[$fichier['id_trace']][] = $fichier;
    }
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Mes réalisations</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="styles/style.css" />
  <style>
    body {
      background: #f9fbff;
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      color: #111;
    }
    main {
      padding-left: 85px;
      transition: padding-left 0.4s ease;
      min-height: 100dvh;
    }
    .sidebar:hover ~ main {
      padding-left: 260px;
    }
    .container {
      max-width: 1100px;
      margin: auto;
      padding: 40px 20px;
    }
    h1 {
      text-align: center;
      font-size: 2rem;
      margin-bottom: 30px;
      color: #4f52ba;
    }
    .filter-form {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  background: #fff;
  padding: 20px;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  margin-bottom: 40px;
  max-height: 1000px;
  transition: all 0.4s ease;
}
    .filter-form select,
    .filter-form button {
      padding: 12px 16px;
      font-size: 1rem;
      border-radius: 10px;
      border: 1px solid #ccc;
      width: 100%;
      box-sizing: border-box;
    }
    .filter-form button {
      background-color: #4f52ba;
      color: white;
      font-weight: bold;
      border: none;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .filter-form button:hover {
      background-color: #3a3db5;
    }
    .cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}
@media (hover: hover) and (pointer: fine) {
  .cards-grid:has(.trace-card:hover) > .trace-card:not(:hover) {
    opacity: 0.4;
    filter: grayscale(30%) brightness(0.95);
    transition: all 0.3s ease;
  }
}

    .trace-card {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  background-color: #fff;
  color: #111;
  transition: transform 0.3s ease, opacity 0.3s ease;
  text-decoration: none;
  display: flex;
  flex-direction: column;
}
    .trace-card:hover {
      transform: translateY(-5px);
    }
    .card-image {
      position: relative;
      background-size: cover;
      background-position: center;
      height: 200px;
    }
    .badge {
      position: absolute;
      top: 12px;
      left: 12px;
      background-color: #4f52ba;
      color: white;
      padding: 4px 12px;
      font-size: 0.75rem;
      border-radius: 12px;
      font-weight: bold;
    }
    
    .card-body {
      padding: 16px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .card-body h3 {
      font-size: 1.2rem;
      font-weight: bold;
      margin: 0 0 8px;
    }
    .card-body .dot {
      color: #4f52ba;
    }
    .card-body p {
      font-size: 0.95rem;
      color: #555;
      margin-bottom: 12px;
    }
    .card-footer {
      font-size: 0.85rem;
      color: #777;
    }
    @media screen and (max-width: 500px) {
      .card-image {
        height: 160px;
      }
      .overlay-title {
        font-size: 1.2rem;
      }
      .card-body h3 {
        font-size: 1rem;
      }
    }
  
    .filter-form.collapsed {
  max-height: 0;
  overflow: hidden;
  opacity: 0;
  padding: 0 !important;
  margin: 0 !important;
  pointer-events: none;
  transition: all 0.4s ease;
}

  .filter-form {
    transition: all 0.3s ease-in-out;
  }
.toggle-filter-btn {
  padding: 10px 16px;
  background-color: #4f52ba;
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: bold;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  transition: background 0.3s ease;
}
.toggle-filter-btn:hover {
  background-color: #3a3db5;
}
.filter-header {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 10px;
}
.arrow-icon {
  display: inline-block;
  transition: transform 0.3s ease;
  margin-right: 6px;
}
@media screen and (max-width: 500px) {
  .cards-grid {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .trace-card {
    width: 100%;
    max-width: 320px;
  }
}

@media screen and (max-width: 768px) {
  .filter-form.collapsed {
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    padding: 0 !important;
    margin: 0 !important;
    pointer-events: none;
    transition: all 0.4s ease;
  }

  .filter-form {
    transition: none !important;
  }
}


@media screen and (max-width: 768px) {
  .filter-form {
    transition: none !important;
  }
}



</style>
</head>
<body>

<main>
  <div class="container">
    <h1>Mes réalisations</h1>
    <!-- FORMULAIRE FILTRE INSÉRÉ ICI AUTOMATIQUEMENT -->
    <div class="filter-header">
  <button onclick="toggleFilters()" class="toggle-filter-btn">
    <span id="toggleIcon" class="arrow-icon">▼</span> Filtres
  </button>
</div>
<form class="filter-form" method="GET">
      <select name="type">
        <option value="">Type de trace</option>
        <?php
          $types = $pdo->query("SELECT * FROM Types_de_traces")->fetchAll();
          foreach ($types as $type) {
            $selected = ($type_filter == $type['id_type']) ? 'selected' : '';
            echo "<option value=\"{$type['id_type']}\" $selected>{$type['nom_type']}</option>";
          }
        ?>
      </select>

      <select name="annee">
        <option value="">Année de BUT</option>
        <option value="1" <?= $annee_filter == 1 ? 'selected' : '' ?>>1</option>
        <option value="2" <?= $annee_filter == 2 ? 'selected' : '' ?>>2</option>
        <option value="3" <?= $annee_filter == 3 ? 'selected' : '' ?>>3</option>
      </select>

      <select name="comp">
        <option value="">Compétence</option>
        <?php
          $comps = ['Comprendre','Concevoir','Exprimer','Développer','Entreprendre'];
          foreach ($comps as $comp) {
            $selected = ($comp_filter == $comp) ? 'selected' : '';
            echo "<option value=\"$comp\" $selected>$comp</option>";
          }
        ?>
      </select>

      <select name="ac">
        <option value="">Apprentissage critique</option>
        <?php
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
          foreach ($ac_list as $group => $options) {
            echo "<optgroup label=\"$group\">";
            foreach ($options as $value => $label) {
              $selected = ($ac_filter == $value) ? 'selected' : '';
              echo "<option value=\"$value\" $selected>$value - $label</option>";
            }
            echo "</optgroup>";
          }
        ?>
      </select>

    <?php if ($userRole === 'concepteur' || ($userRole === 'evaluateur' && $statut_validation == 1)): ?>
      <select name="visibilite">
        <option value="">Visibilité</option>
        <?php
          $visibilites = ['portfolio_public' => 'Public', 'portfolio_academique' => 'Académique'];
          if ($userRole === 'concepteur') {
            $visibilites['prive'] = 'Privé';
          }
          foreach ($visibilites as $key => $label) {
            $selected = ($visibilite_filter === $key) ? 'selected' : '';
            echo "<option value=\"$key\" $selected>$label</option>";
          }
        ?>
      </select>
    <?php endif; ?>


      <select name="order">
        <option value="date_ajout" <?= $order === 'date_ajout' ? 'selected' : '' ?>>+ récent</option>
        <option value="date_ajout_asc" <?= $order === 'date_ajout_asc' ? 'selected' : '' ?>>+ ancien</option>
        <option value="annee_creation_BUT" <?= $order === 'annee_creation_BUT' ? 'selected' : '' ?>>Année BUT</option>
        <option value="titre" <?= $order === 'titre' ? 'selected' : '' ?>>A → Z</option>
      </select>



      <button type="submit">Filtrer</button>
      <button type="reset" onclick="window.location.href=window.location.pathname" style="background-color: #e0e0e0; color: #333;">Réinitialiser</button>
    </form>


    <?php if (empty($traces)): ?>
      <p style="text-align: center;">Aucune trace trouvée.</p>
    <?php else: ?>
      <div class="cards-grid">
        <?php foreach ($traces as $trace): ?>
          <a href="trace.php?id=<?= $trace['id_trace'] ?>" class="trace-card">
  <?php
    $fichier = $fichiersParTrace[$trace['id_trace']][0] ?? null;
    $isVideo = $fichier && $fichier['type_fichier'] === 'video';
  ?>
  <div class="card-image">
   <?php if (!empty($trace['miniature_url'])): ?>
  <img src="<?= htmlspecialchars($trace['miniature_url']) ?>" alt="Miniature" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;" />
<?php elseif ($isVideo): ?>
  <video autoplay muted loop playsinline preload="metadata" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
    <source src="<?= htmlspecialchars($fichier['fichier_url']) ?>" type="video/mp4">
  </video>
<?php else: ?>
  <img src="<?= htmlspecialchars($fichier['fichier_url'] ?? 'default.jpg') ?>" alt="Aperçu" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;" />
<?php endif; ?>


    <span class="badge"><?= htmlspecialchars($trace['nom_type']) ?></span>
  </div>

  <!-- ✅ Bloc manquant à remettre -->
  <div class="card-body">
    <h3><?= htmlspecialchars($trace['titre']) ?> <span class="dot">.</span></h3>
    <p><?= htmlspecialchars(mb_strimwidth($trace['argumentaire_5W'], 0, 120, "...")) ?></p>
    <div class="card-footer">
      <?= strftime('%B %Y', strtotime($trace['date_ajout'])) ?>
    </div>

  </div>
</a>

        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<script>
  function toggleFilters() {
    const form = document.querySelector('.filter-form');
    const icon = document.getElementById('toggleIcon');
    const isCollapsed = form.classList.contains('collapsed');

    if (isCollapsed) {
      form.classList.remove('collapsed');
      icon.style.transform = 'rotate(0deg)';
    } else {
      form.classList.add('collapsed');
      icon.style.transform = 'rotate(180deg)';
    }
  }

  // ✅ Ajout automatique de .collapsed au chargement si mobile
  document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector('.filter-form');
    const icon = document.getElementById('toggleIcon');
    if (window.innerWidth <= 768) {
      form.classList.add('collapsed');
      icon.style.transform = 'rotate(180deg)';
    }
  });
</script>


</body>
</html>