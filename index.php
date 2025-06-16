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
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Portfolio Evan</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="styles/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: #0c0c0c;
      color: white;
    }
    main {
      padding-left: 85px;
      transition: padding-left 0.4s ease;
      min-height: 100dvh;
    }
    .sidebar:hover ~ main {
      padding-left: 260px;
    }
    .hero {
  height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  background: linear-gradient(270deg, #1a1a1a, #0c0c0c, #1a1a1a);
  background-size: 600% 600%;
  animation: backgroundShift 20s ease infinite;
  position: relative;
  overflow: hidden;
  padding: 0 20px;
}
@keyframes backgroundShift {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

    .hero::after {
  content: "";
  position: absolute;
  width: 120vw;
  height: 120vh;
  background: radial-gradient(circle at center, #8e2de2 5%, transparent 70%);
  opacity: 0.08;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  border-radius: 50%;
  z-index: 0;
  pointer-events: none;
}

    @keyframes pulse {
      0%, 100% {
        transform: translate(-50%, -50%) scale(1.2);
        opacity: 0.1;
      }
      50% {
        transform: translate(-50%, -50%) scale(1.6);
        opacity: 0.2;
      }
    }
    .hero-content {
      position: relative;
      z-index: 1;
    }
    .hero h1 {
      font-size: 3.5rem;
      font-weight: 800;
      background: linear-gradient(90deg, #8e2de2, #4a00e0);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 20px;
    }
    .hero p {
      font-size: 1.2rem;
      color: #ccc;
      max-width: 700px;
      margin: 0 auto 30px;
    }
    .cta-btn {
      background: #8e2de2;
      color: white;
      padding: 14px 32px;
      font-weight: 600;
      border-radius: 32px;
      text-decoration: none;
      font-size: 1rem;
      box-shadow: 0 0 16px rgba(142,45,226,0.4);
      transition: 0.3s ease;
      display: inline-block;
      cursor: pointer;
    }
    .cta-btn:hover {
      background: #4a00e0;
      box-shadow: 0 0 24px rgba(142,45,226,0.6);
    }
    .about-section {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 40px;
      max-width: 1100px;
      margin: 0 auto;
      flex-wrap: wrap;
      text-align: left;
      padding: 80px 20px;
    }
    .about-img {
      width: 260px;
      height: auto;
      border-radius: 50%;
      border: 5px solid #8e2de2;
      box-shadow: 0 0 20px rgba(142,45,226,0.4);
      transition: transform 0.3s ease;
    }
    .about-img:hover {
      transform: scale(1.05);
    }
    .about-text h2 {
      font-size: 2rem;
      background: linear-gradient(90deg, #ff00ff, #00ffff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      
    }
    .about-text p {
      color: #ccc;
      font-size: 1.1rem;
      max-width: 600px;
      margin: 20px 0 20px 0;
    }
    .traces-section {
      padding: 80px 20px;
      background: #111;
      color: white;
    }
    .section-title {
      font-size: 2.5rem;
      text-align: center;
      margin-bottom: 10px;
      background: linear-gradient(to right, #8e2de2, #4a00e0);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .section-desc {
      text-align: center;
      margin-bottom: 40px;
      color: #aaa;
    }
    .traces-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 35px;
      max-width: 1300px;
      margin: 0 auto 40px;
    }
    .trace-card {
      background: #1a1a1a;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
      text-decoration: none;
      color: white;
      display: flex;
      flex-direction: column;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .trace-card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 20px 30px rgba(0, 0, 0, 0.4);
    }
    .trace-card img,
    .trace-card video {
      width: 100%;
      height: 260px;
      object-fit: cover;
      display: block;
    }
    .trace-card h3 {
      font-size: 1.3rem;
      margin: 18px 18px 8px;
      font-weight: 700;
      text-decoration: none !important;
    }
    .trace-card .excerpt {
      color: #aaa;
      font-size: 1rem;
      margin: 0 18px 18px;
    }
    .contact-cta {
      text-align: center;
      margin-top: 20px;
    }
    .contact-cta a {
      padding: 12px 24px;
      background: #8e2de2;
      color: white;
      border-radius: 28px;
      font-weight: 600;
      text-decoration: none;
      transition: background 0.3s;
    }
    .contact-cta a:hover {
      background: #4a00e0;
    }
    .footer-contact {
      background: #0c0c0c;
      padding: 60px 20px;
      text-align: center;
      color: #ccc;
    }
    .footer-contact h2 {
      font-size: 2rem;
      margin-bottom: 20px;
    }
    .footer-contact a {
      display: inline-block;
      margin: 10px 15px;
      padding: 12px 20px;
      background: #8e2de2;
      color: white;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      transition: background 0.3s;
    }
    .footer-contact a:hover {
      background: #4a00e0;
    }

    @media screen and (max-width: 768px) {
  .hero h1 {
    font-size: 2rem;
  }

  .hero p {
    font-size: 1rem;
  }

  .about-section {
    flex-direction: column;
    align-items: center;
    padding: 40px 20px;
    text-align: center;
  }

  .about-text p {
    margin: 20px 0;
  }

  .traces-grid {
    grid-template-columns: 1fr;
    gap: 24px;
    padding: 0 10px;
  }

  .cta-btn,
  .footer-contact a {
    width: 90%;
    max-width: 320px;
  }

  .trace-card {
    width: 100%;
  }

  .trace-card img,
  .trace-card video {
    height: auto;
    max-height: 200px;
    object-fit: cover;
  }

  .footer-contact h2 {
    font-size: 1.5rem;
  }
}
.skills-section, .timeline-section {
  padding: 80px 20px;
  background: #0c0c0c;
  text-align: center;
}
.stack-icons {
  display: flex;
  justify-content: center;
  gap: 30px;
  flex-wrap: wrap;
  margin-top: 30px;
}
.stack-icons img {
  width: 50px;
  height: 50px;
  filter: drop-shadow(0 0 6px #8e2de2);
  transition: transform 0.3s ease;
}
.stack-icons img:hover {
  transform: scale(1.1);
}

.timeline {
  max-width: 700px;
  margin: 0 auto;
  padding-top: 40px;
}
.timeline-item {
  position: relative;
  padding-left: 30px;
  margin-bottom: 40px;
  border-left: 3px solid #8e2de2;
}
.timeline-item .dot {
  width: 14px;
  height: 14px;
  background: #8e2de2;
  border-radius: 50%;
  position: absolute;
  left: -8px;
  top: 6px;
}
.timeline-item h3 {
  font-size: 1.1rem;
  color: #fff;
  margin-bottom: 6px;
}
.timeline-item p {
  font-size: 1rem;
  color: #ccc;
}

.scroll-indicator {
      position: absolute;
      bottom: 20px;
      width: 28px;
      height: 45px;
      border: 2px solid white;
      border-radius: 25px;
      display: flex;
      align-items: flex-start;
      justify-content: center;
    }
    .scroll-indicator::before {
      content: '';
      display: block;
      width: 6px;
      height: 6px;
      background: white;
      border-radius: 50%;
      margin-top: 8px;
      animation: scrollMove 2s infinite;
    }
    @keyframes scrollMove {
      0% { transform: translateY(0); opacity: 1; }
      100% { transform: translateY(20px); opacity: 0; }
    }
    [data-aos] {
      opacity: 0;
      transition: opacity 0.6s ease, transform 0.6s ease;
    }
  </style>
</head>
<body>
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main>
  <section class="hero">
    <div class="hero-content" data-aos="fade-up">
      <h1><?= $prenom !== 'Non connecté' ? "Bienvenue $prenom !" : "Bienvenue sur mon Portfolio !" ?></h1>
      <p>Je suis Evan, passionné par le développement web et les projets multimédia modernes. Créatif, curieux et ambitieux.</p>
      <a href="#about" class="cta-btn">Explorer mon univers ↓</a>
    </div>
    <div class="scroll-indicator"></div>
  </section>

  <section class="about-section" id="about">
    <img src="assets/medias/photo_profil_evan_violet.jpg" alt="Photo de Evan" class="about-img" data-aos="zoom-in">
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
            <img src="<?= htmlspecialchars($url) ?>" alt="Media">
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
