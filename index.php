<?php
session_start();
$prenom = $_SESSION['prenom'] ?? 'Non connecté';
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/bdd_connect.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Evan Puthomme-Rostane - Portfolio développeur web & étudiant BUT MMI</title>
  <meta name="description" content="Bienvenue sur le portfolio de Evan Puthomme-Rostane, développeur web créatif et étudiant en BUT MMI. Découvrez ses projets, compétences techniques et réalisations numériques.">
  <meta name="author" content="Evan Puthomme-Rostane">
  <meta name="theme-color" content="#0c0c0c" />

  <!-- Open Graph -->
  <meta property="og:title" content="Evan Puthomme-Rostane - Portfolio développeur web">
  <meta property="og:description" content="Découvrez le portfolio de Evan Puthomme-Rostane, développeur web passionné et étudiant en BUT MMI.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://evan-puthommerostane.fr">
  <meta property="og:image" content="https://evan-puthommerostane.fr/assets/medias/photo_profil_evan_violet.jpg">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Evan Puthomme-Rostane - Portfolio développeur web">
  <meta name="twitter:description" content="Étudiant en BUT MMI spécialisé en développement web et interfaces numériques.">
  <meta name="twitter:image" content="https://evan-puthommerostane.fr/assets/medias/photo_profil_evan_violet.jpg">
  <meta name="twitter:creator" content="@evan_puthomme">

  <!-- Favicon -->
  <link rel="icon" href="https://evan-puthommerostane.fr/assets/favicon.ico" type="image/x-icon">

  <!-- Fonts async -->
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap"></noscript>

  <!-- Styles -->
  <link rel="stylesheet" href="styles/style.css">
  <link rel="stylesheet" href="styles/style_index.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Person",
    "name": "Evan Puthomme-Rostane",
    "url": "https://evan-puthommerostane.fr",
    "jobTitle": "Étudiant en BUT MMI / Développeur Web",
    "sameAs": [
      "https://www.linkedin.com/in/evan-puthomme-rostane",
      "https://github.com/TON_PSEUDO_GITHUB"
    ]
  }
  </script>

  <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>


  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Portfolio Evan Puthomme-Rostane | Développeur Web & Étudiant MMI</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap"></noscript>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="styles/style.css" />
  <link rel="stylesheet" href="styles/style_index.css" />
  <script defer src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script defer src="https://unpkg.com/aos@next/dist/aos.js"></script>

</head>
<body>
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main>
  <section class="hero">
    <div class="hero-content" data-aos="fade-up">
      <h1><?= $prenom !== 'Non connecté' ? "Bienvenue $prenom !" : "Bienvenue sur mon Portfolio !" ?></h1>
      <p>Je suis Evan, passionné par le développement web et les projets multimédia modernes. Créatif, curieux et ambitieux.</p>
      <a href="#about" class="cta-btn">Explore mon univers ↓</a>
    </div>
    <div class="scroll-indicator"></div>
  </section>

  <section class="about-section" id="about">
    <img src="assets/medias/photo_profil_evan_violet.jpg" alt="Photo de Evan" class="about-img" data-aos="zoom-in" loading="lazy">
    <div class="about-text" data-aos="fade-left">
      <h2>Who I am ?</h2>
      <p>Moi c'est Evan, étudiant en BUT MMI. Je me spécialise dans le développement web, tout en explorant la création visuelle, les interfaces, et l'interaction. J'aime transformer des idées en expériences web fluides, animées et créatives.</p>
      <a href="assets/CV/CV_Evan_Puthomme-Rostane.pdf" class="cta-btn" target="_blank">Télécharger mon CV</a>
    </div>
  </section>

  <section class="skills-section" id="skills" data-aos="fade-up">
    <h2 class="section-title">Outils</h2>
    <div class="stack-icons">
      <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg" alt="HTML" loading="lazy">
      <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg" alt="CSS" loading="lazy">
      <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" alt="JavaScript" loading="lazy">
      <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" alt="PHP" loading="lazy">
      <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg" alt="MySQL" loading="lazy">
      <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/figma/figma-original.svg" alt="Figma" loading="lazy">
    </div>
  </section>

  <section class="timeline-section" data-aos="fade-up">
    <h2 class="section-title">Mon parcours</h2>
    <div class="timeline">
      <div class="timeline-item">
        <span class="dot"></span>
        <div class="timeline-content">
          <h3>2024 – Présent : <strong>BUT MMI</strong></h3>
          <p>Couteau Suisse en développement web, design UI/UX, audiovisuel.</p>
        </div>
      </div>
      <div class="timeline-item">
        <span class="dot"></span>
        <div class="timeline-content">
          <h3>2023 - 2024 : <strong>BUT1 GEII</strong></h3>
          <p>Première année axée sur l’exploration de l’électronique, de l’informatique et des bases du développement, à travers des projets concrets mêlant technique, design et travail collaboratif.</p>
        </div>
      </div>
      <div class="timeline-item">
        <span class="dot"></span>
        <div class="timeline-content">
          <h3>2020 - 2023 : <strong>Bac Général Mathématiques & NSI</strong></h3>
          <p>Découverte de la programmation, projets interdisciplinaires orientés tech et design.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="traces-section">
    <h2 class="section-title">Dernières réalisations</h2>
    <p class="section-desc">Voici un aperçu de mes créations récentes : du développement web à la production multimédia.</p>
    <div class="traces-grid">
      <?php
      $stmt = $pdo->prepare("
        SELECT T.id_trace, T.titre, T.date_ajout, T.argumentaire_5W, T.miniature_url
        FROM Traces T
        WHERE T.visibilite = 'portfolio_public' 
          OR (T.visibilite = 'portfolio_academique' AND T.partage_public = 1)
        ORDER BY T.date_ajout DESC
        LIMIT 3
      ");
      $stmt->execute();
      $lastTraces = $stmt->fetchAll();
      foreach ($lastTraces as $trace):
        $id = $trace['id_trace'];
        $title = htmlspecialchars($trace['titre']);
        $excerpt = htmlspecialchars(mb_strimwidth(strip_tags($trace['argumentaire_5W']), 0, 90, '...'));
        $media = $pdo->prepare("SELECT fichier_url, type_fichier FROM Fichiers_traces WHERE id_trace = ? LIMIT 1");
        $media->execute([$id]);
        $file = $media->fetch();
        $url = $file['fichier_url'] ?? 'assets/img/default.jpg';
        $isVideo = isset($file['type_fichier']) && $file['type_fichier'] === 'video';
        if (!empty($trace['miniature_url'])) {
          $url = $trace['miniature_url'];
          $isVideo = false;
        }
      ?>
        <a class="trace-card" href="trace.php?id=<?= $id ?>" data-aos="fade-up">
          <?php if ($isVideo): ?>
            <video autoplay muted loop playsinline src="<?= htmlspecialchars($url) ?>"></video>
          <?php else: ?>
            <img src="<?= htmlspecialchars($url) ?>" alt="Media" loading="lazy">
          <?php endif; ?>
          <h3><?= $title ?></h3>
          <div class="excerpt"><?= $excerpt ?></div>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="contact-cta" style="display: flex; justify-content: center;">
      <a href="mes_realisations.php" class="cta-btn">Voir toutes les réalisations →</a>
    </div>
  </section>

  <section class="footer-contact">
    <h2>Un projet ou une idée ? Discutons-en !</h2>
    <a href="contact.php">Me contacter</a>
    <a href="https://www.linkedin.com/in/evan-puthomme-rostane/" target="_blank">LinkedIn</a>
  </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
  GLightbox({ selector: '.glightbox' });
  AOS.init({ duration: 1000 });
</script>
</body>
</html>
