<?php
// On charge le fichier principal
require_once '../config.php';

// Sécurité : Vérifier que l'utilisateur est connecté et au moins 'editeur'
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] === 'visiteur') {
    header('Location: ' . BASE_URL . 'connexion.php');
    exit;
}

$erreur = '';

// On récupère l'ID de l'article à modifier
$id_article = 0;
if (isset($_GET['id'])) {
    if (ctype_digit($_GET['id'])) {
        $id_article = (int)$_GET['id'];
    }
}

// Si l'ID n'est pas bon, retour à l'accueil
if ($id_article === 0) {
    header('Location: ' . BASE_URL . 'accueil.php');
    exit;
}

// Récupération des catégories pour le MENU SELECT (liste déroulante)
$requete_categories = $pdo->query("SELECT id, nom FROM categories ORDER BY nom");
$categories_list = $requete_categories->fetchAll();

// Gestion de la modification (si le formulaire a été envoyé)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // On récupère et on nettoie les champs
    $titre = "";
    if (isset($_POST['titre'])) {
        $titre = htmlspecialchars(trim($_POST['titre']));
    }

    $description_courte = "";
    if (isset($_POST['description_courte'])) {
        $description_courte = htmlspecialchars(trim($_POST['description_courte']));
    }

    $contenu = "";
    if (isset($_POST['contenu'])) {
        $contenu = htmlspecialchars(trim($_POST['contenu']));
    }

    $id_categorie = "";
    if (isset($_POST['id_categorie'])) {
        $id_categorie = htmlspecialchars(trim($_POST['id_categorie']));
    }

    // On vérifie que tout est rempli
    if (empty($titre) || empty($description_courte) || empty($contenu) || empty($id_categorie)) {
        $erreur = "Tous les champs sont obligatoires.";
    }
    else {
        // C'est bon, on prépare la mise à jour
        $sql = "UPDATE articles 
                SET titre = :titre, description_courte = :desc, contenu = :contenu, id_categorie = :cat 
                WHERE id = :id";
        $requete_maj = $pdo->prepare($sql);

        try {
            $requete_maj->execute([
                ':titre' => $titre,
                ':desc' => $description_courte,
                ':contenu' => $contenu,
                ':cat' => $id_categorie,
                ':id' => $id_article
            ]);

            // Si c'est un succès, on rentre à l'accueil
            header('Location: ' . BASE_URL . 'accueil.php');
            exit;

        }
        catch (PDOException $e) {
            $erreur = "Erreur PDO : " . $e->getMessage();
        }
    }
}

// -----------------------------------------------------
// AFFICHAGE : Récupération de l'article pour pré-remplir le formulaire
// -----------------------------------------------------
$requete_article = $pdo->prepare("SELECT * FROM articles WHERE id = :id");
$requete_article->bindValue(':id', $id_article, PDO::PARAM_INT);
$requete_article->execute();
$article = $requete_article->fetch();

// Rediriger si l'article n'existe pas en base de données
if (!$article) {
    header('Location: ' . BASE_URL . 'accueil.php');
    exit;
}

// Gestion de la conservation de la saisie utilisateur (en cas d'erreur) vs valeurs de la base de données
$val_titre = $article['titre'];
if (isset($_POST['titre'])) {
    $val_titre = $_POST['titre'];
}

$val_desc = $article['description_courte'];
if (isset($_POST['description_courte'])) {
    $val_desc = $_POST['description_courte'];
}

$val_cont = $article['contenu'];
if (isset($_POST['contenu'])) {
    $val_cont = $_POST['contenu'];
}

$val_cat = $article['id_categorie'];
if (isset($_POST['id_categorie'])) {
    $val_cat = $_POST['id_categorie'];
}
?>
<!-- Inclusion de l'en-tête -->
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<main class="wrapper" style="max-width: 800px;">
    <div class="section-head">
        <h2>Modifier cet article</h2>
    </div>

    <?php
// S'il y a une erreur
if ($erreur !== "") {
?>
        <div style="background:var(--accent); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?php echo htmlspecialchars($erreur); ?>
        </div>
    <?php
}
?>

    <!-- Formulaire pré-rempli -->
    <form method="post" id="formModifArt" action="modifier.php?id=<?php echo $id_article; ?>" style="display:flex; flex-direction:column; gap:1.5rem; background:var(--paper-dark); padding:2.5rem; border-radius:4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
        
        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="titre" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase; font-weight: 500;">Titre de l'article</label>
            <input type="text" name="titre" id="titre" required value="<?php echo htmlspecialchars($val_titre); ?>"
                   style="padding: 12px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
            <span class="err-msg" id="err-titre" style="color:var(--accent); font-size:0.8rem; display:none;">Le titre est obligatoire.</span>
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="id_categorie" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase; font-weight: 500;">Catégorie</label>
            <select name="id_categorie" id="id_categorie" required
                    style="padding: 12px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px; background:#fff;">
                <option value="">-- Choisir une catégorie --</option>
                <?php
// On boucle sur toutes les catégories
foreach ($categories_list as $cat) {
?>
                    <option value="<?php echo $cat['id']; ?>" <?php if ($val_cat == $cat['id'])
        echo 'selected'; ?>>
                        <?php echo htmlspecialchars($cat['nom']); ?>
                    </option>
                <?php
}
?>
            </select>
            <span class="err-msg" id="err-cat" style="color:var(--accent); font-size:0.8rem; display:none;">La catégorie est obligatoire.</span>
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="description_courte" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase; font-weight: 500;">Description courte (Résumé)</label>
            <textarea name="description_courte" id="description_courte" required rows="3"
                      style="padding: 12px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px; resize:vertical;"><?php echo htmlspecialchars($val_desc); ?></textarea>
            <span class="err-msg" id="err-desc" style="color:var(--accent); font-size:0.8rem; display:none;">Le résumé est obligatoire.</span>
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="contenu" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase; font-weight: 500;">Contenu de l'article</label>
            <textarea name="contenu" id="contenu" required rows="10"
                      style="padding: 12px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px; resize:vertical;"><?php echo htmlspecialchars($val_cont); ?></textarea>
            <span class="err-msg" id="err-contenu" style="color:var(--accent); font-size:0.8rem; display:none;">Le contenu est obligatoire.</span>
        </div>

        <div style="display:flex; gap:10px; margin-top:10px;">
            <button type="submit" class="btn-page" style="justify-content:center; flex:1; padding: 12px;">
                Mettre à jour l'article
            </button>
            <a href="<?php echo BASE_URL; ?>index.php" class="btn-page" style="justify-content:center; text-align:center; padding: 12px; border-color:var(--ink-faint); color:var(--ink-faint);">
                Annuler
            </a>
        </div>
    </form>
</main>
<script>
// Validation JavaScript côté navigateur
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
        e.preventDefault(); // Annule la soumission si un champ est vide
    }
});
</script>

<footer class="footer">
    &copy; <?php echo date('Y'); ?> La Tribune &mdash; Tous droits réservés
</footer>
</body>
</html>
