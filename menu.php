
<?php
if (!isset($categories)) {
    $categories = $pdo->query("SELECT id, nom FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
}
if (!isset($categorie_filtre)) {
    $categorie_filtre = null;
}
?>
<!-- ======= MASTHEAD ======= -->
<header class="masthead">
    <div class="masthead__top">
        <span class="masthead__date">
            <?= htmlspecialchars(strftime('%A %d %B %Y', time()), ENT_QUOTES, 'UTF-8') ?>
            <!-- ou : <?= date('d/m/Y') ?> si strftime non disponible -->
        </span>
        <span class="masthead__auth">
            <?php if (isset($_SESSION['utilisateur'])): ?>
                Bonjour, <?= htmlspecialchars($_SESSION['utilisateur']['prenom'], ENT_QUOTES, 'UTF-8') ?>
                &nbsp;|&nbsp;
                <?php if ($_SESSION['utilisateur']['role'] !== 'visiteur'): ?>
                    <a href="<?= BASE_URL ?>articles/ajouter.php">+ Article</a> &nbsp;
                    <a href="<?= BASE_URL ?>categories/index.php">Gérer Catégories</a> &nbsp;
                <?php endif; ?>
                <?php if ($_SESSION['utilisateur']['role'] === 'administrateur'): ?>
                    <a href="<?= BASE_URL ?>utilisateurs/index.php">Gérer Utilisateurs</a> &nbsp;
                <?php endif; ?>
                <a href="<?= BASE_URL ?>deconnexion.php">Déconnexion</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>connexion.php">Connexion</a>
            <?php endif; ?>
        </span>
    </div>

    <div class="masthead__logo">
        <h1>La <span>Tribune</span></h1>
        <p class="masthead__tagline">L'information au quotidien &mdash; Indépendante &amp; Rigoureuse</p>
    </div>

    <!-- NAV CATÉGORIES -->
    <nav class="cat-nav">
        <a href="<?= BASE_URL ?>accueil.php" class="<?= $categorie_filtre === null ? 'active' : '' ?>">
            Toutes
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= BASE_URL ?>accueil.php?categorie=<?= $cat['id'] ?>"
               class="<?= $categorie_filtre == $cat['id'] ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['nom'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </nav>
</header>

