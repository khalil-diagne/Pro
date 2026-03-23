<?php
// On charge le fichier principal
require_once '../config.php';

// Vérifier que l'utilisateur est bien un administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'administrateur') {
    header('Location: ' . BASE_URL . 'accueil.php');
    exit;
}

// 1. On récupère et vérifie l'ID de l'utilisateur à supprimer
$id_user = 0;
if (isset($_GET['id'])) {
    if (ctype_digit($_GET['id'])) {
        $id_user = (int) $_GET['id'];
    }
}

// 2. Protection essentielle : empêcher l'administrateur de supprimer son propre compte
if ($id_user === 0 || $id_user === (int) $_SESSION['utilisateur']['id']) {
    header('Location: index.php?err=' . urlencode("Erreur : Tentative de suppression invalide (impossible de supprimer votre compte actuel)."));
    exit;
}

try {
    // 3. Suppression dans la base de données
    $requete = $pdo->prepare("DELETE FROM utilisateurs WHERE id = :id");
    $requete->execute([':id' => $id_user]);
    
    // Succès, retour à la liste
    header('Location: index.php?msg=' . urlencode("L'utilisateur a été supprimé."));
    exit;
    
} catch (PDOException $e) {
    // Si l'utilisateur a écrit des articles, MySQL va empêcher la suppression (code d'erreur 23000 FK Constraint Fails)
    if ($e->getCode() == 23000) {
        header('Location: index.php?err=' . urlencode("Impossible de supprimer cet utilisateur : des articles portent sa signature."));
        exit;
    } else {
        // Autres erreurs base de données
        header('Location: index.php?err=' . urlencode("Erreur de base de données lors de la suppression."));
        exit;
    }
}
?>
