<?php
require_once '../config.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] === 'visiteur') {
    header('Location: ' . BASE_URL . 'connexion.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');

    // Validation PHP Server
    if (empty($nom)) {
        $erreur = "Le nom de la catégorie est requis.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (nom) VALUES (:nom)");
        try {
            $stmt->execute([':nom' => $nom]);
            header('Location: index.php?msg=' . urlencode('Catégorie ajoutée avec succès.'));
            exit;
        } catch (PDOException $e) {
            // Gestion de la contrainte UNIQUE sur 'nom' (Violation = SQLSTATE 23000)
            if ($e->getCode() == 23000) {
                $erreur = "Cette catégorie existe déjà.";
            } else {
                $erreur = "Erreur PDO : " . $e->getMessage();
            }
        }
    }
}
?>
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<main class="wrapper" style="max-width: 500px;">
    <div class="section-head">
        <h2>Ajouter une catégorie</h2>
    </div>

    <?php if ($erreur): ?>
        <div style="background:var(--accent); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

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

<script>
document.getElementById('formAjoutCat').addEventListener('submit', function(e) {
    let nom = document.getElementById('nom').value.trim();
    document.getElementById('err-nom').style.display = 'none';

    if (nom === '') {
        document.getElementById('err-nom').style.display = 'block';
        e.preventDefault();
    }
});
</script>

<footer class="footer">
    &copy; <?= date('Y') ?> La Tribune &mdash; Tous droits réservés
</footer>
</body>
</html>
