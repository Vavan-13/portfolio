<?php
  $hostname = 'localhost';
  /*** mysql hostname ***/
  $username = 'puev4583_concepteur';
  /*** mysql username ***/
  $password = '!PUTev1968';
  /*** mysql password ***/
  $db = 'puev4583_portfolio_evan_2025';
  /*** mysql database ***/
  $dbport = 3307;
  // Data Source Name
  $dsn = "mysql:host=$hostname;dbname=$db;port=$dbport;charset=utf8mb4";
  try {
    $bdd = new PDO($dsn, $username, $password);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connection rÃ©ussie ! </br>";
  } catch (PDOException $e) {
     echo $e->getMessage();
  }

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Portfolio MMI</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="style.css" />
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      min-height: 100vh;
      background: #f0f4ff;
      display: flex;
    }

    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      width: 85px;
      display: flex;
      flex-direction: column;
      background: #161a2d;
      padding: 25px 20px;
      transition: all 0.4s ease;
      z-index: 100;
    }

    .sidebar:hover {
      width: 260px;
    }

    .sidebar-header {
      display: flex;
      align-items: center;
    }

    .sidebar-header img {
      width: 42px;
      border-radius: 50%;
    }

    .sidebar-header h2 {
      color: #fff;
      font-size: 1.25rem;
      font-weight: 600;
      white-space: nowrap;
      margin-left: 23px;
    }

    .sidebar-links {
      list-style: none;
      margin-top: 20px;
      height: 80%;
      overflow-y: auto;
      scrollbar-width: none;
    }

    .sidebar-links::-webkit-scrollbar {
      display: none;
    }

    .sidebar-links h4 {
      color: #fff;
      font-weight: 500;
      white-space: nowrap;
      margin: 10px 0;
      position: relative;
    }

    .sidebar-links h4 span {
      opacity: 0;
    }

    .sidebar:hover .sidebar-links h4 span {
      opacity: 1;
    }

    .sidebar-links .menu-separator {
      position: absolute;
      left: 0;
      top: 50%;
      width: 100%;
      height: 1px;
      transform: translateY(-50%);
      background: #4f52ba;
    }

    .sidebar-links li a {
      display: flex;
      align-items: center;
      gap: 20px;
      color: #fff;
      font-weight: 500;
      white-space: nowrap;
      padding: 15px 10px;
      text-decoration: none;
      transition: 0.2s ease;
    }

    .sidebar-links li a:hover {
      color: #161a2d;
      background: #fff;
      border-radius: 4px;
    }

    .user-account {
      margin-top: auto;
      padding: 12px 10px;
      margin-left: -10px;
    }

    .user-profile {
      display: flex;
      align-items: center;
      color: #161a2d;
    }

    .user-profile img {
      width: 42px;
      border-radius: 50%;
      border: 2px solid #fff;
    }

    .user-detail {
      margin-left: 23px;
      white-space: nowrap;
    }

    .user-detail h3 {
      font-size: 1rem;
      font-weight: 600;
    }

    .user-detail span {
      font-size: 0.775rem;
      font-weight: 600;
      display: block;
    }

    .sidebar:hover .user-account {
      background: #fff;
      border-radius: 4px;
    }

    .user-detail a {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 5px;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-header">
      <img src="logo.jpg" alt="logo" />
      <h2>Portfolio MMI</h2>
    </div>
    <ul class="sidebar-links">
      <h4><span>Menu</span><div class="menu-separator"></div></h4>
      <li><a href="index.php"><span class="material-symbols-outlined"> person </span>À propos</a></li>
      <li><a href="mes_realisations.php"><span class="material-symbols-outlined"> folder </span>Mes Réalisations</a></li>
      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'concepteur'): ?>
        <li><a href="notifications.php"><span class="material-symbols-outlined"> notifications_active </span>Notifications</a></li>
        <li><a href="admin/ajouter_trace.php"><span class="material-symbols-outlined"> add_circle </span>Ajouter une trace</a></li>
        <li><a href="admin/utilisateurs.php"><span class="material-symbols-outlined"> group </span>Gérer utilisateurs</a></li>
        <li><a href="logs.php"><span class="material-symbols-outlined"> history </span>Logs</a></li>
      <?php endif; ?>
    </ul>
    <div class="user-account">
      <div class="user-profile">
        <img src="portrait.jpg" alt="Photo de profil" />
        <div class="user-detail">
          <?php if (isset($_SESSION['prenom'])): ?>
            <h3><?= htmlspecialchars($_SESSION['prenom']) ?></h3>
            <span><?= htmlspecialchars($_SESSION['role']) ?></span>
            <a href="logout.php" class="text-red-600 hover:underline">
              <span class="material-symbols-outlined"> logout </span>Se déconnecter
            </a>
          <?php else: ?>
            <h3>Non connecté</h3>
            <span>Visiteur</span>
            <a href="login.php" class="text-blue-600 hover:underline">
              <span class="material-symbols-outlined"> login </span>Compte
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </aside>
</body>
</html>
