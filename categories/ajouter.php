<?php
// On inclut le fichier de configuration technique
require_once '../config.php';

// On vérifie si l'utilisateur a le droit d'être ici (connecté ET non visiteur)
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] === 'visiteur') {
    // S'il n'a pas le droit, on le renvoie à la page de connexion
    header('Location: ' . BASE_URL . 'connexion.php');
    exit;
}

// Variable pour stocker une éventuelle erreur
$erreur = '';

// Si le formulaire a été validé par l'utilisateur (méthode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // On récupère le nom saisi
    $nom = "";
    if (isset($_POST['nom'])) {
        $nom = trim($_POST['nom']); // trim() enlève les espaces en début et fin de mot
    }

    // On vérifie si le champ est vide
    if (empty($nom)) {
        $erreur = "Le nom de la catégorie est requis.";
    } else {
        // Le champ est rempli, on prépare l'insertion dans la base de données
        $requete = $pdo->prepare("INSERT INTO categories (nom) VALUES (:nom)");
        
        try {
            // On exécute la requête avec le nom fourni
            $requete->execute([':nom' => $nom]);
            
            // Si ça marche, on le renvoie vers la liste avec un message de succès
            header('Location: index.php?msg=' . urlencode('Catégorie ajoutée avec succès.'));
            exit;
            
        } catch (PDOException $e) {
            // S'il y a une erreur dans la base (ex: le nom existe déjà)
            // Le code 23000 correspond à une "violation de contrainte d'unicité"
            if ($e->getCode() == 23000) {
                $erreur = "Cette catégorie existe déjà.";
            } else {
                // Toute autre erreur technique
                $erreur = "Erreur de base de données : " . $e->getMessage();
            }
        }
    }
}
?>
<!-- Inclusion de l'entête et du menu -->
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<!-- Contenu principal de la page -->
<main class="wrapper" style="max-width: 500px;">
    <div class="section-head">
        <h2>Ajouter une catégorie</h2>
    </div>

    <?php 
    // S'il y a un message d'erreur, on l'affiche
    if ($erreur !== '') { 
    ?>
        <div style="background:var(--accent); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?php echo htmlspecialchars($erreur); ?>
        </div>
    <?php 
    } 
    ?>

    <!-- Formulaire d'ajout -->
    <form method="post" id="formAjoutCat" action="ajouter.php" style="display:flex; flex-direction:column; gap:1.5rem; background:var(--paper-dark); padding:2rem; border-radius:4px;">
        
        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="nom" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Nom de la catégorie</label>
            <input type="text" name="nom" id="nom" required 
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

<!-- Validation en Javascript pour un retour immédiat sans recharger la page -->
<script>
document.getElementById('formAjoutCat').addEventListener('submit', function(e) {
    let nom = document.getElementById('nom').value.trim();
    document.getElementById('err-nom').style.display = 'none';

    if (nom === '') {
        document.getElementById('err-nom').style.display = 'block';
        // Si vide, on empêche l'envoi du formulaire par le navigateur
        e.preventDefault();
    }
});
</script>

<footer class="footer">
    &copy; <?php echo date('Y'); ?> La Tribune &mdash; Tous droits réservés
</footer>
</body>
</html>
