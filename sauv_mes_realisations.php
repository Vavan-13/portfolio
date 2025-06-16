<?php
session_start();
require_once __DIR__ . '/includes/init.php';
require_once $basePath . 'includes/bdd_connect.php';
require_once $basePath . 'includes/sidebar.php';

// Filtres
$type_filter = $_GET['type'] ?? '';
$annee_filter = $_GET['annee'] ?? '';
$ac_filter = $_GET['ac'] ?? '';
$comp_filter = $_GET['comp'] ?? '';
$order = $_GET['order'] ?? 'date_ajout';

// Construction de la requête avec filtres
$sql = "
  SELECT T.id_trace, T.titre, T.annee_creation_BUT, T.competence_BUT, T.argumentaire_5W, T.date_ajout, 
         T.apprentissage_critique, TT.nom_type
  FROM Traces T
  JOIN Types_de_traces TT ON T.type_trace_id = TT.id_type
  WHERE 1=1
";

$params = [];
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

$orderBy = in_array($order, ['date_ajout', 'annee_creation_BUT', 'titre']) ? $order : 'date_ajout';
$sql .= " ORDER BY T.$orderBy DESC";

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
  <link rel="stylesheet" href="styles/style.css?v=<?= time(); ?>" />
  <style>
    body {
  font-family: 'Segoe UI', sans-serif;
  background: #f4f7fa;
  margin: 0;
}

main {
  padding-left: 85px;
  background: #f4f7fa;
  min-height: 100vh;
}

.container {
  max-width: 1100px;
  margin: auto;
  padding: 40px 20px;
}

h1 {
  font-size: 2rem;
  margin-bottom: 30px;
  color: #333;
}

.filter-form {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 15px;
  margin-bottom: 30px;
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.04);
}

.filter-form select,
.filter-form button {
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid #ccc;
  background: #fff;
  font-size: 14px;
  cursor: pointer;
}

.filter-form button {
  background: #4f52ba;
  color: #fff;
  font-weight: bold;
  transition: background 0.3s ease;
  border: none;
}

.filter-form button:hover {
  background: #3d3fa1;
}

.trace {
  background: #fff;
  border-radius: 12px;
  padding: 25px;
  margin-bottom: 25px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.05);
  transition: transform 0.2s;
}

.trace:hover {
  transform: translateY(-3px);
}

.trace h3 {
  margin-top: 0;
  color: #4f52ba;
  font-size: 1.4rem;
}

.trace small {
  color: #666;
  display: block;
  margin-bottom: 15px;
  font-size: 0.9rem;
}

.trace p {
  margin-bottom: 12px;
  color: #333;
  line-height: 1.5;
}

.trace strong {
  color: #444;
}

.fichiers {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 15px;
}

.fichiers img,
.fichiers video {
  max-width: 220px;
  max-height: 180px;
  border-radius: 10px;
  border: 1px solid #ddd;
  object-fit: cover;
}

  </style>
</head>
<body>

<main>
  <div class="container">
    <h1>Mes réalisations</h1>

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
          $acs = $pdo->query("SELECT DISTINCT apprentissage_critique FROM Traces WHERE apprentissage_critique IS NOT NULL")->fetchAll();
          foreach ($acs as $ac) {
            $val = htmlspecialchars($ac['apprentissage_critique']);
            $selected = ($ac_filter == $val) ? 'selected' : '';
            echo "<option value=\"$val\" $selected>$val</option>";
          }
        ?>
      </select>

      <select name="order">
        <option value="date_ajout" <?= $order === 'date_ajout' ? 'selected' : '' ?>>Date d’ajout</option>
        <option value="annee_creation_BUT" <?= $order === 'annee_creation_BUT' ? 'selected' : '' ?>>Année BUT</option>
        <option value="titre" <?= $order === 'titre' ? 'selected' : '' ?>>Titre</option>
      </select>

      <button type="submit">Filtrer</button>
    </form>

    <?php if (empty($traces)): ?>
      <p>Aucune trace trouvée.</p>
    <?php else: ?>
      <?php foreach ($traces as $trace): ?>
        <div class="trace">
          <h3><?= htmlspecialchars($trace['titre']) ?></h3>
          <small><?= htmlspecialchars($trace['nom_type']) ?> — BUT<?= $trace['annee_creation_BUT'] ?> — Ajouté le <?= date("d/m/Y", strtotime($trace['date_ajout'])) ?></small>
          <?php if (!empty($trace['competence_BUT'])): ?>
            <p><strong>Compétence :</strong> <?= htmlspecialchars($trace['competence_BUT']) ?></p>
          <?php endif; ?>

          <?php if (!empty($trace['apprentissage_critique'])): ?>
            <p><strong>AC :</strong> <?= htmlspecialchars($trace['apprentissage_critique']) ?></p>
          <?php endif; ?>

          <p><strong>Argumentaire :</strong><br><?= nl2br(htmlspecialchars($trace['argumentaire_5W'])) ?></p>

          <?php if (!empty($fichiersParTrace[$trace['id_trace']])): ?>
            <div class="fichiers">
              <?php foreach ($fichiersParTrace[$trace['id_trace']] as $fichier): ?>
                <?php if ($fichier['type_fichier'] === 'image'): ?>
                  <img src="<?= htmlspecialchars($fichier['fichier_url']) ?>" alt="image">
                <?php elseif ($fichier['type_fichier'] === 'video'): ?>
                  <video controls src="<?= htmlspecialchars($fichier['fichier_url']) ?>"></video>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

</body>
</html>
