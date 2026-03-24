<?php
// On charge le fichier principal
require_once '../config.php';

// Sécurité : Vérifier que l'utilisateur est connecté et a les droits suffisants (éditeur ou administrateur)
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] === 'visiteur') {
    // Si c'est un visiteur ou qu'il n'est pas connecté, retour à la connexion
    header('Location: ' . BASE_URL . 'connexion.php');
    exit;
}

// 1. On récupère l'identifiant (ID) de l'article à supprimer
$id_article = 0;
if (isset($_GET['id'])) {
    if (ctype_digit($_GET['id'])) {
        $id_article = (int) $_GET['id'];
    }
}

// 2. Si l'ID est valide (> 0), on supprime
if ($id_article > 0) {
    try {
        // On prépare la requête de suppression
        $requete = $pdo->prepare("DELETE FROM articles WHERE id = :id");
        $requete->bindValue(':id', $id_article, PDO::PARAM_INT);
        $requete->execute(); // Exécution de la suppression
    } catch (PDOException $e) {
        // En cas d'erreur de base de données (ex: contrainte de clé étrangère), on l'ignore silencieusement ici
        // et l'utilisateur sera de toute façon redirigé vers l'accueil.
    }
}

// 3. Qu'il y ait eu succès ou non, on redirige l'utilisateur vers la page d'accueil (tableau de bord)
header('Location: ' . BASE_URL . 'index.php');
exit;
?>
