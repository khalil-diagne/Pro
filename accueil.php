<?php
// ============================================================
//  accueil.php — Page d'accueil du site d'actualité
//  Affiche les derniers articles avec pagination (10 par page)
// ============================================================
require_once 'config.php';   // connexion PDO   // fonctions utilitaires (ex: estConnecte())

// --- Pagination ---
$articles_par_page = 10;
$page_courante     = isset($_GET['page']) && ctype_digit($_GET['page'])
                     ? (int) $_GET['page']
                     : 1;
if ($page_courante < 1) $page_courante = 1;
$offset = ($page_courante - 1) * $articles_par_page;

// --- Filtre catégorie (optionnel) ---
$categorie_filtre = isset($_GET['categorie']) && ctype_digit($_GET['categorie'])
                    ? (int) $_GET['categorie']
                    : null;

// --- Comptage total pour la pagination ---
if ($categorie_filtre) {
    $stmt_count = $pdo->prepare(
        "SELECT COUNT(*) FROM articles WHERE id_categorie = ?"
    );
    $stmt_count->execute([$categorie_filtre]);
} else {
    $stmt_count = $pdo->query("SELECT COUNT(*) FROM articles");
}
$total_articles = (int) $stmt_count->fetchColumn();
$total_pages    = max(1, (int) ceil($total_articles / $articles_par_page));
if ($page_courante > $total_pages) $page_courante = $total_pages;

// --- Récupération des articles ---
if ($categorie_filtre) {
    $stmt = $pdo->prepare("
        SELECT a.id, a.titre, a.description_courte, a.date_publication,
               c.nom AS categorie, c.id AS cat_id,
               CONCAT(u.prenom, ' ', u.nom) AS auteur
        FROM articles a
        JOIN categories c ON a.id_categorie = c.id
        JOIN utilisateurs u ON a.id_auteur   = u.id
        WHERE a.id_categorie = :cat
        ORDER BY a.date_publication DESC
        LIMIT :limit OFFSET :off
    ");
    $stmt->bindValue(':cat', $categorie_filtre, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $articles_par_page, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("
        SELECT a.id, a.titre, a.description_courte, a.date_publication,
               c.nom AS categorie, c.id AS cat_id,
               CONCAT(u.prenom, ' ', u.nom) AS auteur
        FROM articles a
        JOIN categories c ON a.id_categorie = c.id
        JOIN utilisateurs u ON a.id_auteur   = u.id
        ORDER BY a.date_publication DESC
        LIMIT :limit OFFSET :off
    ");
    $stmt->bindValue(':limit', $articles_par_page, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
}
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Récupération de toutes les catégories (menu filtre) ---
$categories = $pdo->query("SELECT id, nom FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// --- Nom de la catégorie filtrée (pour l'affichage) ---
$nom_categorie_active = '';
if ($categorie_filtre) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $categorie_filtre) {
            $nom_categorie_active = $cat['nom'];
            break;
        }
    }
}

// Couleurs par catégorie
$couleurs_cat = [
    'Technologie' => '#1a6bcc',
    'Sport'       => '#e63c2f',
    'Politique'   => '#2a7a4b',
    'Éducation'   => '#9b4dca',
    'Culture'     => '#d4820a',
];
function couleurCat(string $nom, array $map): string {
    return $map[$nom] ?? '#555';
}
?>
<?php require_once 'entete.php'; ?>
<?php require_once 'menu.php'; ?>

<!-- ======= CONTENU PRINCIPAL ======= -->
<main class="wrapper">

    <div class="section-head">
        <h2>
            <?php if ($nom_categorie_active): ?>
                <?= htmlspecialchars($nom_categorie_active, ENT_QUOTES, 'UTF-8') ?>
            <?php else: ?>
                Dernières actualités
            <?php endif; ?>
        </h2>
        <span class="count">
            <?= $total_articles ?> article<?= $total_articles > 1 ? 's' : '' ?>
            &mdash; page <?= $page_courante ?>/<?= $total_pages ?>
        </span>
    </div>

    <div class="articles-grid">

        <?php if (empty($articles)): ?>
            <div class="empty-state">
                <div class="empty-state__icon">✦</div>
                <p>Aucun article disponible pour le moment.</p>
            </div>

        <?php else: ?>
            <?php foreach ($articles as $index => $article):
                // Choix du style de carte
                if ($index === 0 && !$categorie_filtre) {
                    $card_class = 'article-card--featured';
                } elseif ($index === 0 && $categorie_filtre) {
                    $card_class = 'article-card--featured';
                } elseif ($index < 3 && !$categorie_filtre) {
                    $card_class = 'article-card--secondary';
                } else {
                    $card_class = 'article-card--standard';
                }

                $couleur = couleurCat($article['categorie'], $couleurs_cat);
                $date_fmt = date('d/m/Y à H\hi', strtotime($article['date_publication']));
            ?>
            <article class="article-card <?= $card_class ?>">
                <span class="card__badge" style="background:<?= $couleur ?>">
                    <?= htmlspecialchars($article['categorie'], ENT_QUOTES, 'UTF-8') ?>
                </span>

                <h2 class="card__title">
                    <a href="articles/detail.php?id=<?= (int)$article['id'] ?>">
                        <?= htmlspecialchars($article['titre'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </h2>

                <p class="card__desc">
                    <?= htmlspecialchars($article['description_courte'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <div class="card__meta">
                    <span><?= htmlspecialchars($article['auteur'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="sep">|</span>
                    <span><?= $date_fmt ?></span>
                    <span class="sep">|</span>
                    <a class="card__lire" href="articles/detail.php?id=<?= (int)$article['id'] ?>">
                        Lire &rarr;
                    </a>

                    <?php if (isset($_SESSION['utilisateur']) && in_array($_SESSION['utilisateur']['role'], ['editeur','administrateur'])): ?>
                        <span class="sep">|</span>
                        <a class="card__lire" href="articles/modifier.php?id=<?= (int)$article['id'] ?>">Modifier</a>
                        <span class="sep">|</span>
                        <a class="card__lire"
                           href="articles/supprimer.php?id=<?= (int)$article['id'] ?>"
                           onclick="return confirm('Supprimer cet article ?');"
                           style="color:#c0392b">
                            Supprimer
                        </a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>

    </div><!-- /.articles-grid -->

    <!-- ======= PAGINATION ======= -->
    <?php if ($total_pages > 1): ?>
    <nav class="pagination" aria-label="Pagination">

        <?php
        $base_url = 'accueil.php?' . ($categorie_filtre ? 'categorie=' . $categorie_filtre . '&' : '');
        ?>

        <a class="btn-page <?= $page_courante <= 1 ? 'btn-page--disabled' : '' ?>"
           href="<?= $page_courante > 1 ? $base_url . 'page=' . ($page_courante - 1) : '#' ?>"
           <?= $page_courante <= 1 ? 'aria-disabled="true"' : '' ?>>
            <svg viewBox="0 0 24 24"><path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6z"/></svg>
            Précédent
        </a>

        <span class="pagination__info">
            <?= $page_courante ?> / <?= $total_pages ?>
        </span>

        <a class="btn-page <?= $page_courante >= $total_pages ? 'btn-page--disabled' : '' ?>"
           href="<?= $page_courante < $total_pages ? $base_url . 'page=' . ($page_courante + 1) : '#' ?>"
           <?= $page_courante >= $total_pages ? 'aria-disabled="true"' : '' ?>>
            Suivant
            <svg viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
        </a>

    </nav>
    <?php endif; ?>

</main>

<!-- ======= FOOTER ======= -->
<footer class="footer">
    &copy; <?= date('Y') ?> La Tribune &mdash; Tous droits réservés
    &nbsp;&bull;&nbsp; Propulsé par PHP / MySQL
</footer>

</body>
</html>