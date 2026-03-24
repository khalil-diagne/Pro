<?php
// On charge le fichier principal
require_once '../config.php';

// Sécurité : on vérifie que l'utilisateur est bien autorisé (ni déconnecté, ni simple visiteur)
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] === 'visiteur') {
    // Si l'utilisateur n'a pas les droits, on le renvoie à la page de connexion
    header('Location: ' . BASE_URL . 'connexion.php');
    exit;
}

// Variable pour afficher un éventuel message d'erreur
$erreur = '';

// On récupère la liste des catégories pour pouvoir les afficher dans le menu déroulant du formulaire
$requete_categories = $pdo->query("SELECT id, nom FROM categories ORDER BY nom");
$categories_list = $requete_categories->fetchAll();

// Si l'utilisateur a cliqué sur le bouton "Publier l'article" (méthode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // On nettoie les informations reçues du formulaire
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

    // On vérifie qu'aucun champ n'a été oublié
    if ($titre === '' || $description_courte === '' || $contenu === '' || $id_categorie === '') {
        $erreur = "Tous les champs sont obligatoires.";
    } else {
        // Validation OK, on insère les données dans la base de données
        // NOW() permet de mettre la date et l'heure actuelles
        $sql = "INSERT INTO articles (titre, description_courte, contenu, id_categorie, id_auteur, date_publication) 
                VALUES (:titre, :desc, :contenu, :cat, :auteur, NOW())";
        
        $requete_ajout = $pdo->prepare($sql);
        
        try {
            $requete_ajout->execute([
                ':titre'   => $titre,
                ':desc'    => $description_courte,
                ':contenu' => $contenu,
                ':cat'     => $id_categorie,
                ':auteur'  => $_SESSION['utilisateur']['id'] // On utilise l'ID de l'utilisateur connecté comme auteur
            ]);
            
            // Si l'enregistrement a réussi, on renvoie vers l'accueil
            header('Location: ' . BASE_URL . 'index.php');
            exit;
            
        } catch (PDOException $e) {
            // S'il y a un problème technique lors de la sauvegarde
            $erreur = "Erreur de base de données lors de l'ajout : " . $e->getMessage();
        }
    }
}
?>
<!-- Inclusion de l'entête et du menu de navigation -->
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<main class="wrapper" style="max-width: 800px;">
    <div class="section-head">
        <h2>Ajouter un nouvel article</h2>
    </div>

    <?php 
    // S'il y a eu une erreur, on l'affiche
    if ($erreur !== "") { 
    ?>
        <div style="background:var(--accent); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?php echo htmlspecialchars($erreur); ?>
        </div>
    <?php 
    } 
    ?>

    <!-- Formulaire d'ajout -->
    <form method="post" id="formAjoutArt" action="ajouter.php" style="display:flex; flex-direction:column; gap:1.5rem; background:var(--paper-dark); padding:2.5rem; border-radius:4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
        
        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="titre" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase; font-weight: 500;">Titre de l'article</label>
            <input type="text" name="titre" id="titre" required 
                   style="padding: 12px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
            <span class="err-msg" id="err-titre" style="color:var(--accent); font-size:0.8rem; display:none;">Le titre est obligatoire.</span>
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="id_categorie" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase; font-weight: 500;">Catégorie</label>
            <select name="id_categorie" id="id_categorie" required
                    style="padding: 12px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px; background:#fff;">
                <option value="">-- Choisir une catégorie --</option>
                <?php 
                // On boucle sur la liste des catégories récupérées plus haut
                foreach ($categories_list as $cat) { 
                ?>
                    <option value="<?php echo $cat['id']; ?>">
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
                      style="padding: 12px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px; resize:vertical;"></textarea>
            <span class="err-msg" id="err-desc" style="color:var(--accent); font-size:0.8rem; display:none;">Le résumé est obligatoire.</span>
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="contenu" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase; font-weight: 500;">Contenu de l'article</label>
            <textarea name="contenu" id="contenu" required rows="10"
                      style="padding: 12px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px; resize:vertical;"></textarea>
            <span class="err-msg" id="err-contenu" style="color:var(--accent); font-size:0.8rem; display:none;">Le contenu est obligatoire.</span>
        </div>

        <button type="submit" class="btn-page" style="justify-content:center; margin-top:10px; padding: 12px;">
            Publier l'article
        </button>
    </form>
</main>

<script>
// Validation JavaScript côté navigateur
document.getElementById('formAjoutArt').addEventListener('submit', function(e) {
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
