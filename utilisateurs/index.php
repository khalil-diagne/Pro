<?php
// On charge le fichier principal de configuration
require_once '../config.php';

// Protection Exclusive : seuls les administrateurs ont le droit de voir cette page
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'administrateur') {
    header('Location: ' . BASE_URL . 'accueil.php');
    exit;
}

// 1. On prépare la requête pour récupérer tous les utilisateurs (classés par nom, puis prénom)
$requete_utilisateurs = $pdo->query("SELECT id, nom, prenom, login, role, created_at FROM utilisateurs ORDER BY nom, prenom");

// 2. On récupère toutes les lignes de la base de données
$users = $requete_utilisateurs->fetchAll();

// 3. Gestion des messages (succès ou erreur en provenance des autres pages)
$msg = "";
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
}

$erreur = "";
if (isset($_GET['err'])) {
    $erreur = $_GET['err'];
}
?>
<!-- Inclusion de l'entête et du menu de navigation -->
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<main class="wrapper" style="max-width: 900px;">
    <div class="section-head" style="display:flex; justify-content:space-between; align-items:center;">
        <h2 style="margin:0;">Gestion des Utilisateurs</h2>
        <!-- Bouton pour créer un nouveau compte -->
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

    <!-- Tableau : Liste des utilisateurs -->
    <table style="width:100%; border-collapse:collapse; background:var(--paper-dark); margin-top:1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        
        <thead style="background:var(--ink); color:var(--paper);">
            <tr>
                <th style="padding:12px; text-align:left; font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">ID</th>
                <th style="padding:12px; text-align:left; font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Identité</th>
                <th style="padding:12px; text-align:left; font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Login</th>
                <th style="padding:12px; text-align:left; font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Rôle</th>
                <th style="padding:12px; text-align:center; font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Actions</th>
            </tr>
        </thead>
        
        <tbody>
            <?php 
            // On boucle sur tous les utilisateurs trouvés
            foreach ($users as $u) { 
            ?>
            <tr style="border-bottom:1px solid var(--paper-rule);">
                <td style="padding:12px; font-family:var(--font-mono); font-size:0.9rem;">
                    <?php echo $u['id']; ?>
                </td>
                
                <td style="padding:12px; font-weight:bold; color:var(--ink);">
                    <!-- On assemble le prénom et le nom -->
                    <?php echo htmlspecialchars($u['prenom'] . ' ' . $u['nom']); ?>
                </td>
                
                <td style="padding:12px;">
                    <?php echo htmlspecialchars($u['login']); ?>
                </td>
                
                <td style="padding:12px;">
                    <span class="card__badge" style="background:var(--ink-faint); margin-bottom:0; padding:0.2rem 0.5rem;">
                        <?php echo htmlspecialchars($u['role']); ?>
                    </span>
                </td>
                
                <td style="padding:12px; text-align:center;">
                    <a href="modifier.php?id=<?php echo $u['id']; ?>" style="color:var(--accent2); margin-right:15px; font-size:0.9rem;">Modifier</a>
                    
                    <?php 
                    // Un administrateur ne doit pas pouvoir se supprimer lui-même
                    // On vérifie si l'ID affiché correspond à celui de l'admin actuellement connecté
                    if ($u['id'] !== $_SESSION['utilisateur']['id']) { 
                    ?>
                        <a href="supprimer.php?id=<?php echo $u['id']; ?>" style="color:var(--accent); font-size:0.9rem;" onclick="return confirm('Attention ! Supprimer cet utilisateur est définitif. Continuer ?');">Supprimer</a>
                    <?php 
                    } else { 
                    ?>
                        <!-- Bouton grisé impossible à cliquer -->
                        <span style="color:var(--ink-faint); font-size:0.9rem; cursor:not-allowed;" title="Vous ne pouvez pas vous supprimer vous-même">Supprimer</span>
                    <?php 
                    } 
                    ?>
                </td>
            </tr>
            <?php 
            } // fin foreach 
            ?>
        </tbody>
        
    </table>
</main>

<footer class="footer">
    &copy; <?php echo date('Y'); ?> La Tribune &mdash; Tous droits réservés
</footer>
</body>
</html>
