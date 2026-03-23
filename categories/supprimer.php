<?php
// On charge le fichier principal
require_once '../config.php';

// Vérification de sécurité des accès
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] === 'visiteur') {
    header('Location: ' . BASE_URL . 'connexion.php');
    exit;
}

// 1. On récupère et vérifie l'identifiant (ID) de la catégorie à supprimer
$id_cat = 0;
if (isset($_GET['id'])) {
    if (ctype_digit($_GET['id'])) { // On vérifie que c'est bien composé uniquement de chiffres
        $id_cat = (int) $_GET['id'];
    }
}

// S'il s'agit bien d'un ID valide (> 0)
if ($id_cat > 0) {
    try {
        // On prépare la suppression dans la base de données
        $requete = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $requete->bindValue(':id', $id_cat, PDO::PARAM_INT);
        $requete->execute();
        
        // La suppression a marché, on revient à la liste
        header('Location: index.php?msg=' . urlencode('Catégorie supprimée.'));
        exit;
        
    } catch (PDOException $e) {
        // En cas d'erreur bloquante (exemple : des articles existent encore dans cette catégorie)
        // Le code SQLSTATE 23000 empêche de supprimer un parent si des enfants y sont liés
        if ($e->getCode() == 23000) {
            header('Location: index.php?err=' . urlencode('Impossible de supprimer : des articles sont liés à cette catégorie.'));
            exit;
        } else {
            // Autre erreur diverse
            header('Location: index.php?err=' . urlencode('Erreur lors de la suppression.'));
            exit;
        }
    }
} else {
    // Si l'ID n'était pas valide dès le début, on renvoie à l'index discrètement
    header('Location: index.php');
    exit;
}
?>
