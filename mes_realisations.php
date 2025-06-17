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

$params = [];

$userId = $_SESSION['id_utilisateur'] ?? null;
$userRole = $_SESSION['role'] ?? 'visiteur';
$statut_validation = $_SESSION['statut_validation'] ?? 0;

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

$sql .= " LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$traces = $stmt->fetchAll();

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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Page listant les réalisations de Evan Puthomme-Rostane par type, compétence, AC ou année.">
  <title>Mes réalisations – Evan Puthomme-Rostane</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap"></noscript>
  <link rel="stylesheet" href="styles/style.css">
  <link rel="stylesheet" href="styles/style_mes_realisations.css">
</head>
<body>
<main>
  <div class="container">
    <h1>Mes réalisations</h1>
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
