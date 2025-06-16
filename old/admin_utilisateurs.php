<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Portfolio</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #4f46e5;
      --background: #f9fafb;
      --sidebar: #1e293b;
      --text: #111827;
      --text-light: #6b7280;
      --white: #ffffff;
      --shadow: rgba(0, 0, 0, 0.05) 0px 10px 15px;
      --hover: #6366f1;
      --transition: 0.3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      display: flex;
      min-height: 100vh;
      background-color: var(--background);
      color: var(--text);
    }

    .sidebar {
      width: 250px;
      background-color: var(--sidebar);
      color: var(--white);
      padding: 40px 20px;
      display: flex;
      flex-direction: column;
      gap: 20px;
      position: sticky;
      top: 0;
      height: 100vh;
    }

    .sidebar h2 {
      font-size: 24px;
      font-weight: 700;
      text-align: center;
      margin-bottom: 40px;
    }

    .nav-link {
      display: flex;
      align-items: center;
      padding: 12px 16px;
      text-decoration: none;
      color: var(--white);
      border-radius: 8px;
      transition: background var(--transition);
    }

    .nav-link:hover,
    .nav-link.active {
      background-color: var(--hover);
    }

    .nav-link i {
      margin-right: 10px;
    }

    .main {
      flex: 1;
      padding: 40px;
      overflow-y: auto;
      scroll-behavior: smooth;
    }

    .section-title {
      font-size: 28px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 24px;
    }

    .card {
      background-color: var(--white);
      border-radius: 12px;
      padding: 24px;
      box-shadow: var(--shadow);
      transition: transform var(--transition), box-shadow var(--transition);
      cursor: pointer;
    }

    .card:hover {
      transform: translateY(-6px);
      box-shadow: rgba(0, 0, 0, 0.1) 0px 12px 24px;
    }

    .card h3 {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .card p {
      font-size: 14px;
      color: var(--text-light);
    }

    .footer {
      text-align: center;
      margin-top: 60px;
      font-size: 14px;
      color: var(--text-light);
    }

    a {
      color: var(--primary);
      text-decoration: none;
      transition: color var(--transition);
    }

    a:hover {
      text-decoration: underline;
      color: var(--hover);
    }

    html {
      scroll-behavior: smooth;
    }
  </style>
</head>
<body>
  $1
    <a class="nav-link" href="admin_utilisateurs.php"><i class="fas fa-users-cog"></i> Gérer les profils</a>
    <a class="nav-link" href="#about"><i class="fas fa-user"></i> À propos</a>
    <a class="nav-link" href="#projects"><i class="fas fa-folder-open"></i> Projets</a>
    <a class="nav-link" href="#skills"><i class="fas fa-code"></i> Compétences</a>
    <a class="nav-link" href="#contact"><i class="fas fa-envelope"></i> Contact</a>
  </aside>
  <main class="main">
    <section id="about">
      <h2 class="section-title">À propos</h2>
      <p>Développeur full stack passionné par la création d'expériences numériques modernes et performantes. J’aime fusionner le design et la technologie pour offrir des solutions complètes.</p>
    </section>

    <section id="projects">
      <h2 class="section-title">Projets récents</h2>
      <div class="card-grid">
        <div class="card" tabindex="0">
          <h3>Site Vitrine</h3>
          <p>Développement d’un site d’entreprise responsive avec animations légères.</p>
        </div>
        <div class="card" tabindex="0">
          <h3>Application SaaS</h3>
          <p>Plateforme de gestion avec tableau de bord en temps réel.</p>
        </div>
        <div class="card" tabindex="0">
          <h3>Portfolio interactif</h3>
          <p>Ce site-même, conçu avec une approche moderne type dashboard.</p>
        </div>
      </div>
    </section>

    <section id="skills">
      <h2 class="section-title">Compétences techniques</h2>
      <div class="card-grid">
        <div class="card" tabindex="0">
          <h3>Front-end</h3>
          <p>HTML, CSS, JavaScript, React, TailwindCSS</p>
        </div>
        <div class="card" tabindex="0">
          <h3>Back-end</h3>
          <p>PHP, Node.js, Express, MySQL, MongoDB</p>
        </div>
        <div class="card" tabindex="0">
          <h3>Outils</h3>
          <p>Git, Figma, Docker, Webpack</p>
        </div>
      </div>
    </section>

    <section id="contact">
      <h2 class="section-title">Contact</h2>
      <p>Disponible pour toute collaboration ou opportunité. Écrivez-moi à <a href="mailto:contact@example.com">contact@example.com</a>.</p>
    </section>

    <div class="footer">
      &copy; 2025 Mon Portfolio — Design inspiré des interfaces professionnelles modernes.
    </div>
  </main>
</body>
</html>
