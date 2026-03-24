<?php
// 1. On vérifie si les variables existent déjà. Sinon, on les crée.
if (!isset($categories)) {
    // On prépare une requête pour récupérer les catégories
    $requete = $pdo->query("SELECT id, nom FROM categories ORDER BY nom");

    // On enregistre les résultats dans la variable $categories
    $categories = $requete->fetchAll();
}

if (!isset($categorie_filtre)) {
    // Par défaut, on ne filtre aucune catégorie
    $categorie_filtre = null;
}
?>

<!-- ======= MASTHEAD (En-tête du site) ======= -->
<header class="masthead">
    <div class="masthead__top">
        <span class="masthead__date">
            <?php
// On affiche la date d'aujourd'hui
echo date('d/m/Y');
?>
        </span>
        <span class="masthead__auth">
            <?php
// 2. On vérifie si l'utilisateur est connecté à son compte
if (isset($_SESSION['utilisateur'])) {
?>
                Bonjour, <?php echo htmlspecialchars($_SESSION['utilisateur']['prenom']); ?>
                &nbsp;|&nbsp;
                
                <?php
    // 3. On affiche des liens supplémentaires en fonction du rôle
    // Si l'utilisateur n'est pas un simple "visiteur" (donc un auteur ou admin)
    if ($_SESSION['utilisateur']['role'] !== 'visiteur') {
?>
                    <a href="<?php echo BASE_URL; ?>articles/ajouter.php">+ Article</a> &nbsp;
                    <a href="<?php echo BASE_URL; ?>categories/index.php">Gérer Catégories</a> &nbsp;
                <?php
    }
?>
                
                <?php
    // Si l'utilisateur est le grand administrateur
    if ($_SESSION['utilisateur']['role'] === 'administrateur') {
?>
                    <a href="<?php echo BASE_URL; ?>utilisateurs/index.php">Gérer Utilisateurs</a> &nbsp;
                <?php
    }
?>
                
                <a href="<?php echo BASE_URL; ?>deconnexion.php">Déconnexion</a>
                
            <?php
}
else {
    // S'il n'est pas connecté, on montre le lien pour se connecter
?>
                <a href="<?php echo BASE_URL; ?>connexion.php">Connexion</a>
            <?php
}
?>
        </span>
    </div>

    <div class="masthead__logo">
        <h1> RAKH <span> INFO </span></h1>
        <p class="masthead__tagline">L'information au quotidien &mdash; Indépendante &amp; Rigoureuse</p>
    </div>

    <!-- NAV CATÉGORIES (Menu principal) -->
    <nav class="cat-nav">
        <?php
// 4. On prépare la couleur du menu "Toutes" (actif ou non)
$classeToutes = "";
if ($categorie_filtre === null) {
    $classeToutes = "active";
}
?>
        <a href="<?php echo BASE_URL; ?>index.php" class="<?php echo $classeToutes; ?>">
            Toutes
        </a>
        
        <?php
// 5. On crée un lien pour chaque catégorie qu'on a trouvée dans la base
foreach ($categories as $cat) {

    // On vérifie si on est actuellement sur cette catégorie
    $classeActive = "";
    if ($categorie_filtre == $cat['id']) {
        $classeActive = "active";
    }
?>
            <a href="<?php echo BASE_URL; ?>index.php?categorie=<?php echo $cat['id']; ?>" class="<?php echo $classeActive; ?>">
                <?php echo htmlspecialchars($cat['nom']); ?>
            </a>
        <?php
}
?>
    </nav>
</header>

