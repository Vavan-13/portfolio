<?php
session_start();
session_unset(); // Supprime les variables de session
session_destroy(); // Détruit complètement la session

// Redirection vers la page d'accueil
header('Location: index.php');
exit;
?>