<?php
// On charge le fichier principal
require_once '../config.php';

// 1. On récupère l'ID de l'article à afficher depuis l'URL de manière sécurisée (?id=...)
$id_article = 0;
if (isset($_GET['id'])) {
    if (ctype_digit($_GET['id'])) {
        $id_article = (int) $_GET['id'];
    }
}

// Si l'ID est invalide (égal à 0), on redirige vers l'accueil
if ($id_article === 0) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// 2. On prépare la requête pour aller chercher l'article complet dans la base de données
// On fait des JOIN pour inclure le nom de sa catégorie et le prénom/nom de son auteur
$sql = "
    SELECT a.id, a.titre, a.contenu, a.date_publication,
           c.nom AS categorie, c.id AS cat_id,
           u.prenom, u.nom AS auteur_nom
    FROM articles a
    JOIN categories c ON a.id_categorie = c.id
    JOIN utilisateurs u ON a.id_auteur = u.id
    WHERE a.id = :id
";

$requete = $pdo->prepare($sql);
// On injecte l'ID de l'article de façon sécurisée (PDO)
$requete->bindValue(':id', $id_article, PDO::PARAM_INT);
$requete->execute();

// On stocke le résultat
$article = $requete->fetch();

// Si l'article n'existe pas dans la base de données, on retourne à l'accueil
if (!$article) {
    header('Location: ' . BASE_URL . 'accueil.php');
    exit;
}
?>
<!-- On inclut l'en-tête de la page HTML -->
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<main class="wrapper">
    <!-- Conteneur principal de l'article -->
    <article class="article-detail" style="max-width: 800px; margin: 0 auto; background: var(--paper-dark); padding: 2rem 3rem; border-radius: 4px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        
        <div style="margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between;">
            <!-- Lien de retour -->
            <a href="<?php echo BASE_URL; ?>accueil.php" style="font-family: var(--font-mono); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--ink-faint);">&larr; Retour à l'accueil</a>
            
            <!-- Affichage de la catégorie -->
            <span class="card__badge" style="background:var(--ink); padding:0.3rem 0.6rem; margin-bottom: 0;">
                <?php echo htmlspecialchars($article['categorie']); ?>
            </span>
        </div>

        <!-- Titre -->
        <h1 style="font-family: var(--font-head); font-size: 2.5rem; line-height: 1.25; margin-bottom: 1.5rem; font-weight: 900; color: var(--ink);">
            <?php echo htmlspecialchars($article['titre']); ?>
        </h1>

        <!-- Informations sur la publication (Auteur et Date) -->
        <div class="card__meta" style="margin-bottom: 2.5rem; padding-bottom: 1.25rem; border-bottom: 2px solid var(--paper-rule);">
            <span>
                Par <strong><?php echo htmlspecialchars($article['prenom'] . ' ' . $article['auteur_nom']); ?></strong>
            </span>
            <span class="sep">|</span>
            <span>
                Publié le <?php echo date('d/m/Y à H\hi', strtotime($article['date_publication'])); ?>
            </span>
        </div>

        <!-- Contenu de l'article -->
        <div class="article-content" style="font-size: 1.15rem; line-height: 1.85; color: var(--ink-light);">
            <?php 
            // - nl2br() transforme les simples retours à la ligne de la base de données en balises HTML <br> pour un affichage propre.
            echo nl2br(htmlspecialchars($article['contenu'])); 
            ?>
        </div>

    </article>
</main>

<footer class="footer">
    &copy; <?php echo date('Y'); ?> La Tribune &mdash; Tous droits réservés
</footer>
</body>
</html>
