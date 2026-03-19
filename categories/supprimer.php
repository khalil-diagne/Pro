<?php
require_once '../config.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] === 'visiteur') {
    header('Location: ' . BASE_URL . 'connexion.php');
    exit;
}

$id_cat = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_cat > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->bindValue(':id', $id_cat, PDO::PARAM_INT);
        $stmt->execute();
        
        header('Location: index.php?msg=' . urlencode('Catégorie supprimée.'));
        exit;
    } catch (PDOException $e) {
        // En cas de contrainte de clé étrangère RESTRICT (code SQLSTATE 23000)
        if ($e->getCode() == 23000) {
            header('Location: index.php?err=' . urlencode('Impossible de supprimer : des articles sont liés à cette catégorie.'));
            exit;
        } else {
            header('Location: index.php?err=' . urlencode('Erreur de suppression.'));
            exit;
        }
    }
} else {
    header('Location: index.php');
    exit;
}
?>
