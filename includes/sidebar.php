<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Vérification de connexion
$isConnected = isset($_SESSION['role']) && isset($_SESSION['prenom']);

// Attribuer les valeurs uniquement si connecté
if ($isConnected) {
    $role = $_SESSION['role'];
    $prenom = $_SESSION['prenom'];
} else {
    $role = null;
    $prenom = 'Non connecté';
}
?>

<aside class="sidebar">
  <div class="sidebar-header">
    <img src="assets/medias/logo.jpg" alt="logo" />
    <h2>Portfolio Evan</h2>
  </div>

  <ul class="sidebar-links">
    <h4>
      <span>Menu</span>
      <div class="menu-separator"></div>
    </h4>
    <li><a href="index.php"><span class="material-symbols-outlined"> dashboard </span>À propos</a></li>
    <li><a href="mes_realisations.php"><span class="material-symbols-outlined"> folder </span>Mes Réalisations</a></li>
    <li><a href="contact.php"><span class="material-symbols-outlined"> mail </span>Me contacter</a></li> <!-- Toujours visible -->

    <?php if ($isConnected && $role !== 'concepteur'): ?>
      <li><a href="messagerie.php"><span class="material-symbols-outlined"> forum </span>Messagerie</a></li>
    <?php endif; ?>


    <?php if ($role === 'concepteur'): ?>
      <h4><span>Traces</span><div class="menu-separator"></div></h4>
      <li><a href="ajouter_trace.php"><span class="material-symbols-outlined"> add_circle </span>Ajouter une trace</a></li>
      <li><a href="gerer_traces.php"><span class="material-symbols-outlined"> edit </span>Gérer les traces</a></li>

      <h4><span>Administration</span><div class="menu-separator"></div></h4>
      <!-- <li><a href="notifications.php"><span class="material-symbols-outlined"> notifications_active </span>Notifications</a></li> -->
      <li><a href="utilisateurs.php"><span class="material-symbols-outlined"> groups </span>Gérer utilisateurs</a></li>
      <!-- <li><a href="logs.php"><span class="material-symbols-outlined"> flag </span>Logs</a></li> -->
      <li><a href="messagerie_admin.php"><span class="material-symbols-outlined"> forum </span>Messagerie</a></li>
    <?php endif; ?>

    <h4>
      <span>Compte</span>
      <div class="menu-separator"></div>
    </h4>
    <?php if (!$isConnected): ?>
      <li><a href="login.php"><span class="material-symbols-outlined"> login </span>Connexion</a></li>
    <?php else: ?>
      <li><a href="profil.php"><span class="material-symbols-outlined"> account_circle </span>Profil</a></li>
      <!-- <li><a href="#"><span class="material-symbols-outlined"> settings </span>Paramètres</a></li> -->
      <li><a href="logout.php"><span class="material-symbols-outlined"> logout </span>Se déconnecter</a></li>
    <?php endif; ?>
  </ul>

  <div class="user-account">
    <div class="user-profile">
  <?php
    $photo_profil = null;
$initiales = null;

if ($isConnected) {
    $prenom = $_SESSION['prenom'] ?? '';
    $nom = $_SESSION['nom'] ?? '';
    $role = $_SESSION['role'] ?? '';

    $initiales = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));

    if (!empty($_SESSION['photo_profil']) && $_SESSION['photo_profil'] !== 'default.png') {
        $photo_profil = 'uploads/profils/' . basename($_SESSION['photo_profil']);
    }
}

  ?>
  <?php if ($isConnected): ?>
  <?php if ($photo_profil): ?>
    <img id="sidebar-profile-img" src="<?= htmlspecialchars($photo_profil) ?>" alt="Photo de profil" />
  <?php else: ?>
    <div class="avatar-text"><?= htmlspecialchars($initiales) ?></div>
  <?php endif; ?>
<?php else: ?>
  <img id="sidebar-profile-img" src="assets/medias/icone_user.png" alt="Icône utilisateur" />
<?php endif; ?>


     <div class="user-detail">
        <h3><?= htmlspecialchars($prenom) ?></h3>
        <?php if ($isConnected): ?>
          <span><?= htmlspecialchars($role) ?></span>
        <?php else: ?>
          <span><a href="login.php" style="color: #161a2d">Se connecter</a></span>
        <?php endif; ?>
    </div>

    </div>
  </div>
</aside>
