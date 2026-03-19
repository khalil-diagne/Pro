<?php
require_once '../config.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] === 'visiteur') {
    header('Location: ' . BASE_URL . 'connexion.php');
    exit;
}

$erreur = '';
$id_cat = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_cat === 0) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');

    if (empty($nom)) {
        $erreur = "Le nom de la catégorie est obligatoire.";
    } else {
        $stmt = $pdo->prepare("UPDATE categories SET nom = :nom WHERE id = :id");
        try {
            $stmt->execute([':nom' => $nom, ':id' => $id_cat]);
            header('Location: index.php?msg=' . urlencode('Catégorie modifiée avec succès.'));
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $erreur = "Ce nom de catégorie existe déjà.";
            } else {
                $erreur = "Erreur PDO : " . $e->getMessage();
            }
        }
    }
}

// Fetching current category
$stmt_cat = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
$stmt_cat->execute([':id' => $id_cat]);
$categorie = $stmt_cat->fetch(PDO::FETCH_ASSOC);

if (!$categorie) {
    header('Location: index.php');
    exit;
}

$val_nom = isset($_POST['nom']) ? $_POST['nom'] : $categorie['nom'];
?>
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<main class="wrapper" style="max-width: 500px;">
    <div class="section-head">
        <h2>Modifier la catégorie</h2>
    </div>

    <?php if ($erreur): ?>
        <div style="background:var(--accent); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="post" id="formModifCat" action="modifier.php?id=<?= $id_cat ?>" style="display:flex; flex-direction:column; gap:1.5rem; background:var(--paper-dark); padding:2rem; border-radius:4px;">
        
        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="nom" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Nom</label>
            <input type="text" name="nom" id="nom" required value="<?= htmlspecialchars($val_nom, ENT_QUOTES, 'UTF-8') ?>"
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
    &copy; <?= date('Y') ?> La Tribune &mdash; Tous droits réservés
</footer>
</body>
</html>
