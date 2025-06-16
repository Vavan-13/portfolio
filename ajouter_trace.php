<?php
session_start();
require_once __DIR__ . '/includes/init.php';
require_once $basePath . 'includes/bdd_connect.php';
require_once $basePath . 'includes/sidebar.php';

$success = false;
$partage_public = isset($_POST['partage_public']) ? 1 : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $type_trace = intval($_POST['type_trace'] ?? 0);
    $annee = !empty($_POST['annee_but']) ? intval($_POST['annee_but']) : null;
    $competence = !empty($_POST['competence']) ? trim($_POST['competence']) : null;
    $apprentissage = !empty($_POST['apprentissage_critique']) && is_array($_POST['apprentissage_critique'])
        ? implode(',', array_map('trim', $_POST['apprentissage_critique']))
        : null;
    $argumentaire = trim($_POST['argumentaire_5W'] ?? ''); // ✅ modifié ici
    $visibilite = $_POST['visibilite'] ?? 'portfolio_public';

    if ($titre && $type_trace) {
        $stmt = $pdo->prepare("INSERT INTO Traces (
            titre, type_trace_id, annee_creation_BUT, competence_BUT,
            apprentissage_critique, argumentaire_5W, visibilite, partage_public
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $titre, $type_trace, $annee, $competence,
            $apprentissage, $argumentaire, $visibilite, $partage_public
        ]);

        $id_trace = $pdo->lastInsertId();
        $uploadDir = 'uploads/traces/trace_' . $id_trace . '/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if (!empty($_FILES['fichier']['tmp_name'][0])) {
            foreach ($_FILES['fichier']['tmp_name'] as $i => $tmpName) {
                if (!empty($tmpName)) {
                    $originalName = $_FILES['fichier']['name'][$i];
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov'])) continue;

                    $safeName = 'trace_' . $id_trace . '_' . ($i + 1) . '.' . $ext;
                    $uploadPath = $uploadDir . $safeName;

                    if (move_uploaded_file($tmpName, $uploadPath)) {
                        $type = in_array($ext, ['mp4', 'mov']) ? 'video' : 'image';
                        $pdo->prepare("INSERT INTO Fichiers_traces (id_trace, fichier_url, type_fichier) VALUES (?, ?, ?)")
                            ->execute([$id_trace, $uploadPath, $type]);
                    }
                }
            }
        }
        if (!empty($_FILES['miniature']['tmp_name'])) {
    $miniaturePath = 'uploads/traces/trace_' . $id_trace . '/miniature_' . time() . '.jpg';
    
    // Crée le dossier si besoin
    if (!is_dir(dirname($miniaturePath))) {
        mkdir(dirname($miniaturePath), 0777, true);
    }

    // Sauvegarde du fichier
    move_uploaded_file($_FILES['miniature']['tmp_name'], $miniaturePath);

    // Mise à jour de la trace avec le chemin de la miniature
    $stmt = $pdo->prepare("UPDATE Traces SET miniature_url = ? WHERE id_trace = ?");
    $stmt->execute([$miniaturePath, $id_trace]);
}


        $success = true;
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Ajouter une trace</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="styles/style.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
  <style>
    main {
      padding-left: 85px;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: #f5f7fa;
    }
    .form-box {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 600px;
    }
    .form-box h2 {
      margin-bottom: 10px;
      text-align: center;
      color: #4f52ba;
    }
    .form-box p.success {
      text-align: center;
      color: green;
      font-weight: bold;
      margin-bottom: 15px;
    }
    .form-box input,
    .form-box select,
    .form-box textarea {
      width: 100%;
      margin-bottom: 15px;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }
    .form-box button {
      background: #4f52ba;
      color: white;
      border: none;
      padding: 12px;
      font-weight: bold;
      font-size: 1rem;
      border-radius: 8px;
      cursor: pointer;
      width: 100%;
    }
    .form-box button:hover {
      background: #3c3fab;
    }
    .choices__item--selectable {
      background: #4f52ba !important;
      color: #fff !important;
      border-radius: 20px !important;
      padding: 6px 12px !important;
      margin: 4px 6px 4px 0;
      font-size: 0.95rem !important;
      font-weight: 500;
    }
    .choices__item--selectable::after,
    .choices__button {
      background-image: none !important;
    }
    @media screen and (max-width: 600px) {
      .choices {
        display: none !important;
      }
      #apprentissage_critique {
        display: block !important;
      }
    }
    .styled-checkbox {
  position: relative;
  padding-left: 32px;
  cursor: pointer;
  font-size: 0.95rem;
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
  top: 2px;
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
}

.styled-checkbox .checkmark:after {
  left: 6px;
  top: 2px;
  width: 5px;
  height: 10px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

  </style>
</head>
<body>

<main>
  <div class="form-box">
    <h2>Ajouter une trace</h2>

    <?php if ($success): ?>
      <p class="success">✅ Trace ajoutée avec succès !</p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="traceForm">
      <input type="text" name="titre" placeholder="Titre" required>

      <select name="type_trace" required>
        <option value="">-- Type de trace --</option>
        <option value="1">Infographie</option>
        <option value="2">Graphisme</option>
        <option value="3">Développement Web</option>
        <option value="4">Vidéo</option>
        <option value="5">Photographie</option>
        <option value="6">UI/UX Design</option>
        <option value="7">Projet tutoré</option>
        <option value="8">Communication</option>
        <option value="9">Réseaux sociaux</option>
      </select>

      <input type="number" name="annee_but" placeholder="Année de BUT (facultatif)" min="1" max="3">

      <label for="competence">Compétence</label>
      <select name="competence" id="competence" multiple>
        <option value="">-- Choisissez une compétence (facultatif) --</option>
        <option value="Comprendre">Comprendre</option>
        <option value="Concevoir">Concevoir</option>
        <option value="Exprimer">Exprimer</option>
        <option value="Développer">Développer</option>
        <option value="Entreprendre">Entreprendre</option>
      </select>

      <label for="apprentissage_critique">Apprentissages Critiques</label>
      <select name="apprentissage_critique[]" id="apprentissage_critique" multiple>
        <optgroup label="COMPRENDRE">
          <option value="AC11.01">AC11.01 - Présenter une organisation</option>
          <option value="AC11.02">AC11.02 - Évaluer un site</option>
          <option value="AC11.03">AC11.03 - Produire des analyses statistiques</option>
          <option value="AC11.04">AC11.04 - Analyser des formes médiatiques</option>
          <option value="AC11.05">AC11.05 - Identifier les cibles</option>
          <option value="AC11.06">AC11.06 - Réaliser des entretiens utilisateurs</option>
        </optgroup>
        <optgroup label="CONCEVOIR">
          <option value="AC12.01">AC12.01 - Concevoir un produit</option>
          <option value="AC12.02">AC12.02 - Construire la valeur d’un produit</option>
          <option value="AC12.03">AC12.03 - Recommandation marketing</option>
          <option value="AC12.04">AC12.04 - Stratégie de communication</option>
        </optgroup>
        <optgroup label="EXPRIMER">
          <option value="AC13.01">AC13.01 - Écrire pour les médias numériques</option>
          <option value="AC13.02">AC13.02 - Pistes graphiques</option>
          <option value="AC13.03">AC13.03 - Composer et retoucher des visuels</option>
          <option value="AC13.04">AC13.04 - Tourner et monter une vidéo</option>
          <option value="AC13.05">AC13.05 - Designer une interface</option>
          <option value="AC13.06">AC13.06 - Optimiser les médias</option>
        </optgroup>
        <optgroup label="DÉVELOPPER">
          <option value="AC14.01">AC14.01 - Environnement de développement</option>
          <option value="AC14.02">AC14.02 - Pages Web fluides</option>
          <option value="AC14.03">AC14.03 - Générer à partir de données</option>
          <option value="AC14.04">AC14.04 - Mettre en ligne une app</option>
          <option value="AC14.05">AC14.05 - Modéliser des données</option>
          <option value="AC14.06">AC14.06 - Déployer avec CMS/MVC</option>
        </optgroup>
        <optgroup label="ENTREPRENDRE">
          <option value="AC15.01">AC15.01 - Gérer un projet</option>
          <option value="AC15.02">AC15.02 - Budgétiser un projet</option>
          <option value="AC15.03">AC15.03 - Découvrir des écosystèmes</option>
          <option value="AC15.04">AC15.04 - Analyser un produit innovant</option>
          <option value="AC15.05">AC15.05 - Présence en ligne pro</option>
          <option value="AC15.06">AC15.06 - Interagir en organisation</option>
          <option value="AC15.07">AC15.07 - Message professionnel</option>
        </optgroup>
      </select>
      <label for="argumentaire_5W">Description / Argumentaire</label>
<textarea name="argumentaire_5W" id="argumentaire_5W" placeholder="Présentez les 5W, ou ajoutez un commentaire sur la trace... (facultatif)"></textarea>

      <label for="visibilite">Visibilité</label>
      <select name="visibilite" required>
        <option value="portfolio_public">Portfolio Public</option>
        <option value="portfolio_academique">Portfolio Académique</option>
        <option value="prive">Privé</option>
      </select>

      <div id="partagePublicContainer" style="display:none; margin-bottom: 15px;">
  <label class="styled-checkbox">
    <input type="checkbox" name="partage_public" value="1">
    <span class="checkmark"></span>
    Afficher aussi dans le portfolio public
  </label>
</div>


<label for="miniature">Médias</label>
 <input type="file" name="fichier[]" accept=".jpg,.jpeg,.png,.gif,.mp4,.mov" multiple>

<label for="miniature">Miniature (optionnelle)</label>
<input type="file" name="miniature" accept="image/*">

<progress id="progressBar" value="0" max="100" style="width: 100%; display: none;"></progress>
<button type="submit">Ajouter la trace</button>
</form>
</div>


</main>

<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
  const isMobile = window.innerWidth <= 600;
  if (!isMobile) {
    const select = document.getElementById('apprentissage_critique');
    new Choices(select, {
      removeItemButton: true,
      placeholder: 'true',
      placeholderValue: 'Sélectionnez un ou plusieurs AC...',
      searchPlaceholderValue: 'Rechercher...',
      shouldSort: false
    });
  }

  document.getElementById('traceForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();
    const progressBar = document.getElementById('progressBar');

    xhr.upload.addEventListener('progress', function(e) {
      if (e.lengthComputable) {
        const percent = Math.round((e.loaded / e.total) * 100);
        progressBar.style.display = 'block';
        progressBar.value = percent;
      }
    });

    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        progressBar.value = 100;

        // Supprimer ancien message s'il existe
        const oldSuccess = document.querySelector('.form-box .success');
        if (oldSuccess) oldSuccess.remove();

        // Créer et afficher le nouveau message de succès
        const formBox = document.querySelector('.form-box');
        const successMsg = document.createElement('p');
        successMsg.className = 'success';
        successMsg.innerText = '✅ Trace ajoutée avec succès !';
        formBox.insertBefore(successMsg, formBox.querySelector('form'));

        // Réinitialiser le formulaire
        form.reset();

        // Réinitialiser les choix si plugin actif
        if (!isMobile) {
          const choices = document.querySelectorAll('.choices');
          choices.forEach(c => c.innerHTML = '');
        }

        // Cacher la barre de progression après un court délai
        setTimeout(() => progressBar.style.display = 'none', 1000);
      }
    };

    xhr.open('POST', form.action);
    xhr.send(formData);
  });
</script>
<script>
  const visibiliteSelect = document.querySelector('select[name="visibilite"]');
  const partageContainer = document.getElementById('partagePublicContainer');

  function updatePartageVisibility() {
    partageContainer.style.display = visibiliteSelect.value === 'portfolio_academique' ? 'block' : 'none';
  }

  // Initialise à l'ouverture de la page
  updatePartageVisibility();

  // Met à jour à chaque changement
  visibiliteSelect.addEventListener('change', updatePartageVisibility);
</script>



</body>
</html>
