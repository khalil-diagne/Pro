<?php
// ============================================================
//  accueil.php — Page d'accueil du site d'actualité
//  Affiche les derniers articles avec pagination (10 par page)
// ============================================================
require_once 'config.php';

// --- 1. Pagination (gestion des pages) ---
$articles_par_page = 10;

// On vérifie sur quelle page on est actuellement
$page_courante = 1;
if (isset($_GET['page'])) {
    if (ctype_digit($_GET['page'])) { // Vérifie que c'est bien un nombre
        $page_courante = (int)$_GET['page'];
    }
}

// On s'assure que la page n'est pas inférieure à 1
if ($page_courante < 1) {
    $page_courante = 1;
}

// On calcule d'où on commence à lire les articles dans la base de données
$offset = ($page_courante - 1) * $articles_par_page;

// --- 2. Filtre catégorie (optionnel) ---
$categorie_filtre = null;
if (isset($_GET['categorie'])) {
    if (ctype_digit($_GET['categorie'])) {
        $categorie_filtre = (int)$_GET['categorie'];
    }
}

// --- 3. Comptage total pour la pagination ---
if ($categorie_filtre !== null) {
    // Si on a choisi une catégorie précise
    $requete_comptage = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE id_categorie = :cat");
    $requete_comptage->bindValue(':cat', $categorie_filtre);
    $requete_comptage->execute();
}
else {
    // Si on veut tous les articles
    $requete_comptage = $pdo->query("SELECT COUNT(*) FROM articles");
}

// On récupère le nombre total d'articles qui correspondent
$total_articles = (int)$requete_comptage->fetchColumn();

// On calcule le nombre total de pages nécessaires
$total_pages = ceil($total_articles / $articles_par_page);
if ($total_pages < 1) {
    $total_pages = 1;
}

// Si la page demandée est plus grande que le total de pages, on bloque à la dernière page
if ($page_courante > $total_pages) {
    $page_courante = $total_pages;
}

// --- 4. Récupération des articles à afficher ---
if ($categorie_filtre !== null) {
    // On récupère les articles de la catégorie spécifique
    $requete_articles = $pdo->prepare("
        SELECT a.id, a.titre, a.description_courte, a.date_publication,
               c.nom AS categorie, c.id AS cat_id,
               CONCAT(u.prenom, ' ', u.nom) AS auteur
        FROM articles a
        JOIN categories c ON a.id_categorie = c.id
        JOIN utilisateurs u ON a.id_auteur = u.id
        WHERE a.id_categorie = :cat
        ORDER BY a.date_publication DESC
        LIMIT :limit OFFSET :off
    ");
    $requete_articles->bindValue(':cat', $categorie_filtre, PDO::PARAM_INT);
    $requete_articles->bindValue(':limit', $articles_par_page, PDO::PARAM_INT);
    $requete_articles->bindValue(':off', $offset, PDO::PARAM_INT);
    $requete_articles->execute();
}
else {
    // On récupère les articles de toutes les catégories
    $requete_articles = $pdo->prepare("
        SELECT a.id, a.titre, a.description_courte, a.date_publication,
               c.nom AS categorie, c.id AS cat_id,
               CONCAT(u.prenom, ' ', u.nom) AS auteur
        FROM articles a
        JOIN categories c ON a.id_categorie = c.id
        JOIN utilisateurs u ON a.id_auteur = u.id
        ORDER BY a.date_publication DESC
        LIMIT :limit OFFSET :off
    ");
    $requete_articles->bindValue(':limit', $articles_par_page, PDO::PARAM_INT);
    $requete_articles->bindValue(':off', $offset, PDO::PARAM_INT);
    $requete_articles->execute();
}

$articles = $requete_articles->fetchAll(PDO::FETCH_ASSOC);

// --- 5. Récupération de toutes les catégories (pour le menu) ---
$requete_categories = $pdo->query("SELECT id, nom FROM categories ORDER BY nom");
$categories = $requete_categories->fetchAll(PDO::FETCH_ASSOC);

// --- 6. Trouver le nom de la catégorie active ---
$nom_categorie_active = '';
if ($categorie_filtre !== null) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $categorie_filtre) {
            $nom_categorie_active = $cat['nom'];
            break;
        }
    }
}

// 7. Options de couleurs pour chaque catégorie
$couleurs_cat = [
    'Technologie' => '#1a6bcc',
    'Sport' => '#e63c2f',
    'Politique' => '#2a7a4b',
    'Éducation' => '#9b4dca',
    'Culture' => '#d4820a',
];

// Fonction simple pour trouver la bonne couleur
function couleurCat($nom, $tableau_couleurs)
{
    // Si la catégorie est dans notre tableau, on renvoie sa couleur, sinon une couleur par défaut gris
    if (isset($tableau_couleurs[$nom])) {
        return $tableau_couleurs[$nom];
    }
    else {
        return '#555';
    }
}
?>

<!-- On inclut ici l'entête HTML (la balise <head> avec le CSS) et le menu principal -->
<?php require_once 'entete.php'; ?>
<?php require_once 'menu.php'; ?>

<!-- ======= CONTENU PRINCIPAL ======= -->
<main class="wrapper">

    <div class="section-head">
        <h2>
            <?php
if ($nom_categorie_active !== '') {
    echo htmlspecialchars($nom_categorie_active);
}
else {
    echo "Dernières actualités";
}
?>
        </h2>
        
        <span class="count">
            <?php
// On gère le "s" pour préciser s'il y a 1 ou plusieurs article(s)
$mot_article = "article";
if ($total_articles > 1) {
    $mot_article = "articles";
}

echo $total_articles . " " . $mot_article . " &mdash; page " . $page_courante . "/" . $total_pages;
?>
        </span>
    </div>

    <div class="articles-grid">

        <?php
// Si on n'a trouvé aucun article
if (empty($articles)) {
?>
            <div class="empty-state">
                <div class="empty-state__icon">✦</div>
                <p>Aucun article disponible pour le moment.</p>
            </div>
        <?php
}
else {
    // Sinon, on affiche chaque article tour à tour
    foreach ($articles as $index => $article) {

        // On détermine quel style utiliser pour mettre en évidence le premier article
        $card_class = 'article-card--standard';

        if ($index === 0) {
            $card_class = 'article-card--featured'; // Très grand article principal
        }
        elseif ($index < 3 && $categorie_filtre === null) {
            $card_class = 'article-card--secondary'; // Articles secondaires de taille moyenne
        }

        // On récupère sa couleur
        $couleur = couleurCat($article['categorie'], $couleurs_cat);

        // On formate la date d'affichage
        $date_fmt = date('d/m/Y à H\hi', strtotime($article['date_publication']));
?>
            
            <!-- Carte pour un article -->
            <article class="article-card <?php echo $card_class; ?>">
                
                <!-- Badge Catégorie -->
                <span class="card__badge" style="background:<?php echo $couleur; ?>">
                    <?php echo htmlspecialchars($article['categorie']); ?>
                </span>

                <!-- Titre de l'article avec lien -->
                <h2 class="card__title">
                    <a href="articles/detail.php?id=<?php echo (int)$article['id']; ?>">
                        <?php echo htmlspecialchars($article['titre']); ?>
                    </a>
                </h2>

                <!-- Description courte -->
                <p class="card__desc">
                    <?php echo htmlspecialchars($article['description_courte']); ?>
                </p>
                <!-- Informations supplémentaires de l'article -->
                <div class="card__meta">
                    <span><?php echo htmlspecialchars($article['auteur']); ?></span>
                    <span class="sep">|</span>
                    <span><?php echo $date_fmt; ?></span>
                    <span class="sep">|</span>
                    <a class="card__lire" href="articles/detail.php?id=<?php echo (int)$article['id']; ?>">
                        Lire &rarr;
                    </a>

                    <?php
        // Si l'utilisateur a le droit (éditeur ou administrateur), on affiche les boutons d'action
        if (isset($_SESSION['utilisateur'])) {
            $role = $_SESSION['utilisateur']['role'];
            if ($role === 'editeur' || $role === 'administrateur') {
?>
                            <span class="sep">|</span>
                            <a class="card__lire" href="articles/modifier.php?id=<?php echo (int)$article['id']; ?>">Modifier</a>
                            <span class="sep">|</span>
                            <a class="card__lire"
                               href="articles/supprimer.php?id=<?php echo (int)$article['id']; ?>"
                               onclick="return confirm('Confirmez-vous la suppression de cet article ?');"
                               style="color:#c0392b">
                                Supprimer
                            </a>
                    <?php
            }
        }
?>
                </div>
            </article>
            
            <?php
    } // fin foreach 
} // fin if empty
?>

    </div>

    <!-- ======= PAGINATION (Les boutons pour aller aux pages suivantes) ======= -->
    <?php
// On n'affiche la pagination que si on a plus d'une seule page
if ($total_pages > 1) {
?>
    <nav class="pagination" aria-label="Pagination">

        <?php
    // On construit l'URL de base (ex: accueil.php?categorie=2&)
    $base_url = 'index.php?';
    if ($categorie_filtre !== null) {
        $base_url = 'index.php?categorie=' . $categorie_filtre . '&';
    }

    // On détermine si le bouton précédent doit être désactivé
    $classeBtnPrecedent = "btn-page";
    $lienPrecedent = "#";
    if ($page_courante <= 1) {
        $classeBtnPrecedent = "btn-page btn-page--disabled"; // Désactivé (on est sur la page 1)
    }
    else {
        $lienPrecedent = $base_url . 'page=' . ($page_courante - 1);
    }

    // On détermine si le bouton suivant doit être désactivé
    $classeBtnSuivant = "btn-page";
    $lienSuivant = "#";
    if ($page_courante >= $total_pages) {
        $classeBtnSuivant = "btn-page btn-page--disabled"; // Désactivé (on est sur la dernière page)
    }
    else {
        $lienSuivant = $base_url . 'page=' . ($page_courante + 1);
    }
?>

        <a class="<?php echo $classeBtnPrecedent; ?>" href="<?php echo $lienPrecedent; ?>">
            <svg viewBox="0 0 24 24"><path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6z"/></svg>
            Précédent
        </a>

        <span class="pagination__info">
            <?php echo $page_courante . " / " . $total_pages; ?>
        </span>

        <a class="<?php echo $classeBtnSuivant; ?>" href="<?php echo $lienSuivant; ?>">
            Suivant
            <svg viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
        </a>

    </nav>
    <?php
} // fin if total_pages
?>

</main>

<!-- ======= FOOTER ======= -->
<footer class="footer">
    &copy; <?php echo date('Y'); ?> RAKH INFO  &mdash; Tous droits réservés
    &nbsp;&bull;&nbsp;
</footer>

</body>
</html>