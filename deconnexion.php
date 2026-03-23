<?php
// On fait toujours ça pour avoir accès à la variable $_SESSION
session_start();

// On vide toutes les données de la session actuelle
session_unset();

// On détruit la session pour déconnecter l'utilisateur
session_destroy();

// On redirige l'utilisateur vers la page d'accueil
header("Location: accueil.php");
// On arrête l'exécution du reste du code avec exit par sécurité
exit;
?>
