<?php
require_once '../config.php';

// Sécurité : Vérifier que l'utilisateur est connecté et au moins 'editeur'
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] === 'visiteur') {
    header('Location: ' . BASE_URL . 'connexion.php');
    exit;
}

$erreur = '';
$id_article = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_article === 0) {
    header('Location: ' . BASE_URL . 'accueil.php');
    exit;
}

// Récupération des catégories pour le MENU SELECT
$categories_stmt = $pdo->query("SELECT id, nom FROM categories ORDER BY nom");
$categories_list = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description_courte = trim($_POST['description_courte'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $id_categorie = $_POST['id_categorie'] ?? '';

    // Validation PHP Server-side
    if (empty($titre) || empty($description_courte) || empty($contenu) || empty($id_categorie)) {
        $erreur = "Tous les champs sont obligatoires.";
    } else {
        // Enregistrement
        $sql = "UPDATE articles 
                SET titre = :titre, description_courte = :desc, contenu = :contenu, id_categorie = :cat 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([
                ':titre'   => $titre,
                ':desc'    => $description_courte,
                ':contenu' => $contenu,
                ':cat'     => $id_categorie,
                ':id'      => $id_article
            ]);
            
            header('Location: ' . BASE_URL . 'accueil.php');
            exit;
        } catch (PDOException $e) {
            $erreur = "Erreur PDO : " . $e->getMessage();
        }
    }
}

// Récupération de l'article pour pré-remplir le formulaire
$stmt_article = $pdo->prepare("SELECT * FROM articles WHERE id = :id");
$stmt_article->bindValue(':id', $id_article, PDO::PARAM_INT);
$stmt_article->execute();
$article = $stmt_article->fetch(PDO::FETCH_ASSOC);

// Rediriger si l'article n'existe pas
if (!$article) {
    header('Location: ' . BASE_URL . 'accueil.php');
    exit;
}

// Gestion de la conservation de la saisie utilisateur (en cas d'erreur) vs valeurs base
$val_titre = isset($_POST['titre']) ? $_POST['titre'] : $article['titre'];
$val_desc  = isset($_POST['description_courte']) ? $_POST['description_courte'] : $article['description_courte'];
$val_cont  = isset($_POST['contenu']) ? $_POST['contenu'] : $article['contenu'];
$val_cat   = isset($_POST['id_categorie']) ? $_POST['id_categorie'] : $article['id_categorie'];
?>
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<main class="wrapper" style="max-width: 800px;">
    <div class="section-head">
        <h2>Modifier cet article</h2>
    </div>

    <?php if ($erreur): ?>
        <div style="background:var(--accent); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="post" id="formModifArt" action="modifier.php?id=<?= $id_article ?>" style="display:flex; flex-direction:column; gap:1.5rem; background:var(--paper-dark); padding:2.5rem; border-radius:4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
        
        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="titre" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase; font-weight: 500;">Titre de l'article</label>
            <input type="text" name="titre" id="titre" required value="<?= htmlspecialchars($val_titre, ENT_QUOTES, 'UTF-8') ?>"
                   style="padding: 12px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
            <span class="err-msg" id="err-titre" style="color:var(--accent); font-size:0.8rem; display:none;">Le titre est obligatoire.</span>
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="id_categorie" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase; font-weight: 500;">Catégorie</label>
            <select name="id_categorie" id="id_categorie" required
                    style="padding: 12px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px; background:#fff;">
                <option value="">-- Choisir une catégorie --</option>
                <?php foreach ($categories_list as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $val_cat == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nom'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="err-msg" id="err-cat" style="color:var(--accent); font-size:0.8rem; display:none;">La catégorie est obligatoire.</span>
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="description_courte" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase; font-weight: 500;">Description courte (Résumé)</label>
            <textarea name="description_courte" id="description_courte" required rows="3"
                      style="padding: 12px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px; resize:vertical;"><?= htmlspecialchars($val_desc, ENT_QUOTES, 'UTF-8') ?></textarea>
            <span class="err-msg" id="err-desc" style="color:var(--accent); font-size:0.8rem; display:none;">Le résumé est obligatoire.</span>
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="contenu" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase; font-weight: 500;">Contenu de l'article</label>
            <textarea name="contenu" id="contenu" required rows="10"
                      style="padding: 12px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px; resize:vertical;"><?= htmlspecialchars($val_cont, ENT_QUOTES, 'UTF-8') ?></textarea>
            <span class="err-msg" id="err-contenu" style="color:var(--accent); font-size:0.8rem; display:none;">Le contenu est obligatoire.</span>
        </div>

        <div style="display:flex; gap:10px; margin-top:10px;">
            <button type="submit" class="btn-page" style="justify-content:center; flex:1; padding: 12px;">
                Mettre à jour l'article
            </button>
            <a href="<?= BASE_URL ?>accueil.php" class="btn-page" style="justify-content:center; text-align:center; padding: 12px; border-color:var(--ink-faint); color:var(--ink-faint);">
                Annuler
            </a>
        </div>
    </form>
</main>

<script>
// Validation JS côté client
document.getElementById('formModifArt').addEventListener('submit', function(e) {
    let hasError = false;
    let titre = document.getElementById('titre').value.trim();
    let cat = document.getElementById('id_categorie').value.trim();
    let desc = document.getElementById('description_courte').value.trim();
    let contenu = document.getElementById('contenu').value.trim();
    
    document.querySelectorAll('.err-msg').forEach(el => el.style.display = 'none');

    if (titre === '') { document.getElementById('err-titre').style.display = 'block'; hasError = true; }
    if (cat === '') { document.getElementById('err-cat').style.display = 'block'; hasError = true; }
    if (desc === '') { document.getElementById('err-desc').style.display = 'block'; hasError = true; }
    if (contenu === '') { document.getElementById('err-contenu').style.display = 'block'; hasError = true; }

    if (hasError) {
        e.preventDefault(); // Annule la soumission
    }
});
</script>

<footer class="footer">
    &copy; <?= date('Y') ?> La Tribune &mdash; Tous droits réservés
</footer>
</body>
</html>
