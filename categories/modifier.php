<?php
// On charge le fichier principal
require_once '../config.php';

// Sécurité : l'utilisateur a-t-il le droit d'être ici ?
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] === 'visiteur') {
    header('Location: ' . BASE_URL . 'connexion.php');
    exit;
}

$erreur = '';

// On regarde quel ID on veut modifier dans l'URL (?id=...)
$id_cat = 0;
if (isset($_GET['id'])) {
    if (ctype_digit($_GET['id'])) {
        $id_cat = (int) $_GET['id'];
    }
}

// Si l'ID est invalide, on le renvoie à la liste
if ($id_cat === 0) {
    header('Location: index.php');
    exit;
}

// Si le formulaire est validé (On veut sauvegarder)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // On nettoie le nom reçu
    $nom = "";
    if (isset($_POST['nom'])) {
        $nom = htmlspecialchars(trim($_POST['nom']));
    }

    if (empty($nom)) {
        $erreur = "Le nom de la catégorie est obligatoire.";
    } else {
        // On modifie l'enregistrement dans la table
        $requete_update = $pdo->prepare("UPDATE categories SET nom = :nom WHERE id = :id");
        try {
            $requete_update->execute([':nom' => $nom, ':id' => $id_cat]);
            
            // Succès
            header('Location: index.php?msg=' . urlencode('Catégorie modifiée avec succès.'));
            exit;
            
        } catch (PDOException $e) {
            // Un nom de catégorie unique (si on a choisi un nom qui existe déjà par exemple)
            if ($e->getCode() == 23000) {
                $erreur = "Ce nom de catégorie existe déjà.";
            } else {
                $erreur = "Erreur PDO : " . $e->getMessage();
            }
        }
    }
}

// On récupère les informations actuelles de la catégorie (avant modification)
$requete_cat = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
$requete_cat->execute([':id' => $id_cat]);
$categorie = $requete_cat->fetch();

// Si aucune catégorie n'a été trouvée avec cet ID
if (!$categorie) {
    header('Location: index.php');
    exit;
}

// Si l'utilisateur a tenté d'envoyer un nom (même avec une erreur), on l'affiche
// Sinon, on met le nom actuel de la catégorie qui est dans la base
$val_nom = $categorie['nom'];
if (isset($_POST['nom'])) {
    $val_nom = $_POST['nom'];
}
?>
<!-- En-tête de page -->
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<main class="wrapper" style="max-width: 500px;">
    <div class="section-head">
        <h2>Modifier la catégorie</h2>
    </div>

    <?php 
    // Affichage d'une erreur technique si besoin
    if ($erreur !== "") { 
    ?>
        <div style="background:var(--accent); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?php echo htmlspecialchars($erreur); ?>
        </div>
    <?php 
    } 
    ?>

    <!-- Formulaire pré-rempli -->
    <form method="post" id="formModifCat" action="modifier.php?id=<?php echo $id_cat; ?>" style="display:flex; flex-direction:column; gap:1.5rem; background:var(--paper-dark); padding:2rem; border-radius:4px;">
        
        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="nom" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Nom</label>
            <!-- L'attribut 'value' affiche le texte par défaut -->
            <input type="text" name="nom" id="nom" required value="<?php echo htmlspecialchars($val_nom); ?>"
                   style="padding: 10px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
            <span id="err-nom" style="color:var(--accent); font-size:0.8rem; display:none;">Ce champ est requis</span>
        </div>

        <div style="display:flex; gap:10px; margin-top:10px;">
            <button type="submit" class="btn-page" style="justify-content:center; flex:1;">
                Enregistrer
            </button>
            <a href="index.php" class="btn-page" style="justify-content:center; border-color:var(--ink-faint); color:var(--ink-faint);">
                Annuler
            </a>
        </div>
    </form>
</main>

<!-- Validation facile côté client -->
<script>
document.getElementById('formModifCat').addEventListener('submit', function(e) {
    let nom = document.getElementById('nom').value.trim();
    document.getElementById('err-nom').style.display = 'none';

    if (nom === '') {
        document.getElementById('err-nom').style.display = 'block';
        e.preventDefault();
    }
});
</script>

<footer class="footer">
    &copy; <?php echo date('Y'); ?>RAKH INFO &mdash; Tous droits réservés
</footer>
</body>
</html>
