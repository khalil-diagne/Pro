<?php
require_once '../config.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'administrateur') {
    header('Location: ' . BASE_URL . 'accueil.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom          = trim($_POST['nom'] ?? '');
    $prenom       = trim($_POST['prenom'] ?? '');
    $login        = trim($_POST['login'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $role         = $_POST['role'] ?? 'visiteur';

    if (empty($nom) || empty($prenom) || empty($login) || empty($mot_de_passe)) {
        $erreur = "Tous les champs sont requis.";
    } elseif (!in_array($role, ['visiteur', 'editeur', 'administrateur'])) {
        $erreur = "Rôle invalide.";
    } else {
        $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, login, mot_de_passe, role) VALUES (:nom, :prenom, :login, :mdp, :role)");
        try {
            $stmt->execute([
                ':nom'    => $nom,
                ':prenom' => $prenom,
                ':login'  => $login,
                ':mdp'    => $hash,
                ':role'   => $role
            ]);
            header('Location: index.php?msg=' . urlencode('Utilisateur ajouté.'));
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $erreur = "Ce login ($login) est déjà utilisé.";
            } else {
                $erreur = "Erreur PDO : " . $e->getMessage();
            }
        }
    }
}
?>
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<main class="wrapper" style="max-width: 600px;">
    <div class="section-head">
        <h2>Ajouter un utilisateur</h2>
    </div>

    <?php if ($erreur): ?>
        <div style="background:var(--accent); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="post" id="formAjoutUser" action="ajouter.php" style="display:flex; flex-direction:column; gap:1.5rem; background:var(--paper-dark); padding:2rem; border-radius:4px;">
        
        <div style="display:flex; gap:1rem;">
            <div style="flex:1; display:flex; flex-direction:column; gap:.5rem;">
                <label for="prenom" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Prénom</label>
                <input type="text" name="prenom" id="prenom" required 
                       style="padding: 10px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
                <span class="err-msg" id="err-prenom" style="color:var(--accent); font-size:0.8rem; display:none;">Ce champ est requis</span>
            </div>

            <div style="flex:1; display:flex; flex-direction:column; gap:.5rem;">
                <label for="nom" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Nom</label>
                <input type="text" name="nom" id="nom" required 
                       style="padding: 10px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
                <span class="err-msg" id="err-nom" style="color:var(--accent); font-size:0.8rem; display:none;">Ce champ est requis</span>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="login" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Nom d'utilisateur (Login)</label>
            <input type="text" name="login" id="login" required 
                   style="padding: 10px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
            <span class="err-msg" id="err-login" style="color:var(--accent); font-size:0.8rem; display:none;">Ce champ est requis</span>
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="mot_de_passe" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Mot de passe</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" required 
                   style="padding: 10px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
            <span class="err-msg" id="err-mdp" style="color:var(--accent); font-size:0.8rem; display:none;">Ce champ est requis</span>
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="role" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Rôle</label>
            <select name="role" id="role" required style="padding: 10px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
                <option value="visiteur">Visiteur (Consultation uniquement)</option>
                <option value="editeur">Éditeur (Gère articles & catégories)</option>
                <option value="administrateur">Administrateur (Gère accès & tout)</option>
            </select>
        </div>

        <div style="display:flex; gap:10px; margin-top:10px;">
            <button type="submit" class="btn-page" style="justify-content:center; flex:1;">
                Enregistrer l'utilisateur
            </button>
            <a href="index.php" class="btn-page" style="justify-content:center; border-color:var(--ink-faint); color:var(--ink-faint);">
                Annuler
            </a>
        </div>
    </form>
</main>

<script>
document.getElementById('formAjoutUser').addEventListener('submit', function(e) {
    let hasError = false;
    let prenom = document.getElementById('prenom').value.trim();
    let nom    = document.getElementById('nom').value.trim();
    let login  = document.getElementById('login').value.trim();
    let mdp    = document.getElementById('mot_de_passe').value.trim();
    
    document.querySelectorAll('.err-msg').forEach(el => el.style.display = 'none');

    if (prenom === '') { document.getElementById('err-prenom').style.display = 'block'; hasError = true; }
    if (nom === '')    { document.getElementById('err-nom').style.display = 'block'; hasError = true; }
    if (login === '')  { document.getElementById('err-login').style.display = 'block'; hasError = true; }
    if (mdp === '')    { document.getElementById('err-mdp').style.display = 'block'; hasError = true; }

    if (hasError) {
        e.preventDefault();
    }
});
</script>

<footer class="footer">
    &copy; <?= date('Y') ?> La Tribune &mdash; Tous droits réservés
</footer>
</body>
</html>
