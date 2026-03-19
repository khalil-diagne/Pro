<?php
require_once '../config.php';

// Récupération de l'ID depuis l'URL de manière sécurisée
$id_article = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_article === 0) {
    header('Location: ' . BASE_URL . 'accueil.php');
    exit;
}

// Requête préparée -> prévention des injections SQL
$stmt = $pdo->prepare("
    SELECT a.id, a.titre, a.contenu, a.date_publication,
           c.nom AS categorie, c.id AS cat_id,
           CONCAT(u.prenom, ' ', u.nom) AS auteur
    FROM articles a
    JOIN categories c ON a.id_categorie = c.id
    JOIN utilisateurs u ON a.id_auteur = u.id
    WHERE a.id = :id
");
$stmt->bindValue(':id', $id_article, PDO::PARAM_INT);
$stmt->execute();

$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    // Si l'article n'existe pas ou l'ID est invalide, redirection
    header('Location: ' . BASE_URL . 'accueil.php');
    exit;
}
?>
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<main class="wrapper">
    <article class="article-detail" style="max-width: 800px; margin: 0 auto; background: var(--paper-dark); padding: 2rem 3rem; border-radius: 4px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        
        <div style="margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between;">
            <a href="<?= BASE_URL ?>accueil.php" style="font-family: var(--font-mono); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--ink-faint);">&larr; Retour à l'accueil</a>
            
            <span class="card__badge" style="background:var(--ink); padding:0.3rem 0.6rem; margin-bottom: 0;">
                <?= htmlspecialchars($article['categorie'], ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>

        <h1 style="font-family: var(--font-head); font-size: 2.5rem; line-height: 1.25; margin-bottom: 1.5rem; font-weight: 900; color: var(--ink);">
            <?= htmlspecialchars($article['titre'], ENT_QUOTES, 'UTF-8') ?>
        </h1>

        <div class="card__meta" style="margin-bottom: 2.5rem; padding-bottom: 1.25rem; border-bottom: 2px solid var(--paper-rule);">
            <span>Par <strong><?= htmlspecialchars($article['auteur'], ENT_QUOTES, 'UTF-8') ?></strong></span>
            <span class="sep">|</span>
            <span>Publié le <?= date('d/m/Y à H\hi', strtotime($article['date_publication'])) ?></span>
        </div>

        <div class="article-content" style="font-size: 1.15rem; line-height: 1.85; color: var(--ink-light);">
            <!-- On utilise nl2br pour afficher les \n en <br>. htmlspecialchars empêche l'exécution de script malveillant (XSS) -->
            <?= nl2br(htmlspecialchars($article['contenu'], ENT_QUOTES, 'UTF-8')) ?>
        </div>

    </article>
</main>

<footer class="footer">
    &copy; <?= date('Y') ?> La Tribune &mdash; Tous droits réservés
</footer>
</body>
</html>
