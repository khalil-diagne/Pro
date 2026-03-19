<?php
require_once '../config.php';

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'administrateur') {
    header('Location: ' . BASE_URL . 'accueil.php');
    exit;
}

$id_user = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;

// Protection: empecher l'admin de supprimer son propre compte
if ($id_user === 0 || $id_user === (int) $_SESSION['utilisateur']['id']) {
    header('Location: index.php?err=' . urlencode('Erreur : Tentative de suppression invalide (impossible de supprimer votre compte actuel).'));
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = :id");
    $stmt->execute([':id' => $id_user]);
    
    header('Location: index.php?msg=' . urlencode('L\'utilisateur a été supprimé.'));
    exit;
} catch (PDOException $e) {
    // Si l'utilisateur est auteur d'articles (SQLSTATE 23000 FK Constraint Fails)
    if ($e->getCode() == 23000) {
        header('Location: index.php?err=' . urlencode('Impossible de supprimer cet utilisateur : des articles portent sa signature.'));
        exit;
    } else {
        header('Location: index.php?err=' . urlencode('Erreur PDO lors de la suppression.'));
        exit;
    }
}
?>
