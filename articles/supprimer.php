<?php
require_once '../config.php';

// Sécurité : Vérifier que l'utilisateur est connecté et au moins 'editeur'
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] === 'visiteur') {
    header('Location: ' . BASE_URL . 'connexion.php');
    exit;
}

$id_article = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_article > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = :id");
        $stmt->bindValue(':id', $id_article, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        // En cas d'erreur de contrainte clé étrangère ou autre, on passe silencieusement 
        // ou on pourrait gérer une redirection avec message d'erreur.
    }
}

// Redirection vers l'accueil après suppression
header('Location: ' . BASE_URL . 'accueil.php');
exit;
?>
