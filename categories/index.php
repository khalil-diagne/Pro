<?php
// On inclut le fichier de configuration
require_once '../config.php';

// Vérification des droits d'accès
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] === 'visiteur') {
    header('Location: ' . BASE_URL . 'connexion.php');
    exit;
}

// 1. On prépare la requête pour récupérer toutes les catégories
$requete_categories = $pdo->query("SELECT id, nom, created_at FROM categories ORDER BY nom");

// 2. On stocke le résultat sous forme de tableau
$cats = $requete_categories->fetchAll();

// Gestion des messages de retour (affichés après un ajout ou une suppression)
$msg = "";
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
}

$erreur = "";
if (isset($_GET['err'])) {
    $erreur = $_GET['err'];
}
?>
<!-- Inclusion de l'entête et du menu -->
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<main class="wrapper" style="max-width: 800px;">
    
    <div class="section-head" style="display:flex; justify-content:space-between; align-items:center;">
        <h2 style="margin:0;">Gestion des catégories</h2>
        <!-- Bouton pour créer une nouvelle catégorie -->
        <a href="ajouter.php" class="btn-page" style="padding:0.4rem 1rem;">Nouveau</a>
    </div>

    <?php 
    // S'il y a un message de succès
    if ($msg !== "") { 
    ?>
        <div style="background:var(--accent2); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php 
    } 
    ?>
    
    <?php 
    // S'il y a un message d'erreur
    if ($erreur !== "") { 
    ?>
        <div style="background:var(--accent); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?php echo htmlspecialchars($erreur); ?>
        </div>
    <?php 
    } 
    ?>

    <!-- Tableau listant les catégories -->
    <table style="width:100%; border-collapse:collapse; background:var(--paper-dark); margin-top:1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        
        <thead style="background:var(--ink); color:var(--paper);">
            <tr>
                <th style="padding:12px; text-align:left; font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">ID</th>
                <th style="padding:12px; text-align:left; font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Nom</th>
                <th style="padding:12px; text-align:center; font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Actions</th>
            </tr>
        </thead>
        
        <tbody>
            <?php 
            // S'il n'y a aucune catégorie
            if (empty($cats)) { 
            ?>
                <tr>
                    <td colspan="3" style="padding:20px; text-align:center;">Aucune catégorie existante.</td>
                </tr>
            <?php 
            } else { 
                // Sinon on boucle sur chaque catégorie
                foreach ($cats as $c) { 
            ?>
                <tr style="border-bottom:1px solid var(--paper-rule);">
                    <td style="padding:12px; font-family:var(--font-mono); font-size:0.9rem;">
                        <?php echo $c['id']; ?>
                    </td>
                    
                    <td style="padding:12px; font-weight:bold; color:var(--ink);">
                        <?php echo htmlspecialchars($c['nom']); ?>
                    </td>
                    
                    <td style="padding:12px; text-align:center;">
                        <a href="modifier.php?id=<?php echo $c['id']; ?>" style="color:var(--accent2); margin-right:15px; font-size:0.9rem;">Modifier</a>
                        <a href="supprimer.php?id=<?php echo $c['id']; ?>" style="color:var(--accent); font-size:0.9rem;" onclick="return confirm('Confirmer la suppression de cette catégorie ?\n\nNB: Si des articles sont liés à cette catégorie, ils empêcheront sa suppression.');">Supprimer</a>
                    </td>
                </tr>
            <?php 
                } // fin du foreach
            } // fin du else
            ?>
        </tbody>
        
    </table>
</main>

<footer class="footer">
    &copy; <?php echo date('Y'); ?> La Tribune &mdash; Tous droits réservés
</footer>
</body>
</html>
