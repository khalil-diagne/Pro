<?php
require_once '../config.php';

// Protection Administrateur Exclusive
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'administrateur') {
    header('Location: ' . BASE_URL . 'accueil.php');
    exit;
}

$erreur = '';
$id_user = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_user === 0) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom          = trim($_POST['nom'] ?? '');
    $prenom       = trim($_POST['prenom'] ?? '');
    $login        = trim($_POST['login'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? ''; // Optionnel
    $role         = $_POST['role'] ?? 'visiteur';

    // Sécurité: Un administrateur ne peut pas se retirer ses propres droits par mégarde
    if ($id_user === $_SESSION['utilisateur']['id'] && $role !== 'administrateur') {
        $erreur = "Vous ne pouvez pas retirer vos propres privilèges d'administrateur.";
    } elseif (empty($nom) || empty($prenom) || empty($login)) {
        $erreur = "Le nom, le prénom et le login sont obligatoires.";
    } elseif (!in_array($role, ['visiteur', 'editeur', 'administrateur'])) {
        $erreur = "Rôle invalide attribué.";
    } else {
        try {
            if (!empty($mot_de_passe)) {
                // Modification AVEC nouveau mot de passe
                $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = :n, prenom = :p, login = :l, role = :r, mot_de_passe = :m WHERE id = :id");
                $stmt->execute([':n'=>$nom, ':p'=>$prenom, ':l'=>$login, ':r'=>$role, ':m'=>$hash, ':id'=>$id_user]);
            } else {
                // Modification SANS changer le mot de passe
                $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = :n, prenom = :p, login = :l, role = :r WHERE id = :id");
                $stmt->execute([':n'=>$nom, ':p'=>$prenom, ':l'=>$login, ':r'=>$role, ':id'=>$id_user]);
            }
            
            // Si l'utilisateur modifie ses propres infos, mettre à jour la session active
            if ($id_user === $_SESSION['utilisateur']['id']) {
                $_SESSION['utilisateur']['nom'] = $nom;
                $_SESSION['utilisateur']['prenom'] = $prenom;
            }

            header('Location: index.php?msg=' . urlencode('Utilisateur modifié avec succès.'));
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $erreur = "Ce login ($login) est déjà utilisé par un autre compte.";
            } else {
                $erreur = "Erreur PDO : " . $e->getMessage();
            }
        }
    }
}

// Récupérer les informations actuelles
$stmt_user = $pdo->prepare("SELECT nom, prenom, login, role FROM utilisateurs WHERE id = :id");
$stmt_user->execute([':id' => $id_user]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: index.php');
    exit;
}

$val_nom    = isset($_POST['nom']) ? $_POST['nom'] : $user['nom'];
$val_prenom = isset($_POST['prenom']) ? $_POST['prenom'] : $user['prenom'];
$val_login  = isset($_POST['login']) ? $_POST['login'] : $user['login'];
$val_role   = isset($_POST['role']) ? $_POST['role'] : $user['role'];
?>
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<main class="wrapper" style="max-width: 600px;">
    <div class="section-head">
        <h2>Modifier l'utilisateur</h2>
    </div>

    <?php if ($erreur): ?>
        <div style="background:var(--accent); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="post" id="formModifUser" action="modifier.php?id=<?= $id_user ?>" style="display:flex; flex-direction:column; gap:1.5rem; background:var(--paper-dark); padding:2rem; border-radius:4px;">
        
        <div style="display:flex; gap:1rem;">
            <div style="flex:1; display:flex; flex-direction:column; gap:.5rem;">
                <label for="prenom" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Prénom</label>
                <input type="text" name="prenom" id="prenom" required value="<?= htmlspecialchars($val_prenom, ENT_QUOTES, 'UTF-8') ?>"
                       style="padding: 10px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
                <span class="err-msg" id="err-prenom" style="color:var(--accent); font-size:0.8rem; display:none;">Ce champ est requis</span>
            </div>

            <div style="flex:1; display:flex; flex-direction:column; gap:.5rem;">
                <label for="nom" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Nom</label>
                <input type="text" name="nom" id="nom" required value="<?= htmlspecialchars($val_nom, ENT_QUOTES, 'UTF-8') ?>"
                       style="padding: 10px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
                <span class="err-msg" id="err-nom" style="color:var(--accent); font-size:0.8rem; display:none;">Ce champ est requis</span>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="login" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Nom d'utilisateur (Login)</label>
            <input type="text" name="login" id="login" required value="<?= htmlspecialchars($val_login, ENT_QUOTES, 'UTF-8') ?>"
                   style="padding: 10px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
            <span class="err-msg" id="err-login" style="color:var(--accent); font-size:0.8rem; display:none;">Ce champ est requis</span>
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="mot_de_passe" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Nouveau mot de passe <span style="text-transform:none; color:var(--ink-faint);">(Laisser vide pour conserver l'actuel)</span></label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" 
                   style="padding: 10px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="role" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Rôle</label>
            <select name="role" id="role" required style="padding: 10px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px; background:#fff;">
                <option value="visiteur" <?= $val_role === 'visiteur' ? 'selected' : '' ?>>Visiteur (Consultation uniquement)</option>
                <option value="editeur" <?= $val_role === 'editeur' ? 'selected' : '' ?>>Éditeur (Gère articles & catégories)</option>
                <option value="administrateur" <?= $val_role === 'administrateur' ? 'selected' : '' ?>>Administrateur (Gère accès & tout)</option>
            </select>
        </div>

        <div style="display:flex; gap:10px; margin-top:10px;">
            <button type="submit" class="btn-page" style="justify-content:center; flex:1;">
                Mettre à jour
            </button>
            <a href="index.php" class="btn-page" style="justify-content:center; border-color:var(--ink-faint); color:var(--ink-faint);">
                Annuler
            </a>
        </div>
    </form>
</main>

<script>
document.getElementById('formModifUser').addEventListener('submit', function(e) {
    let hasError = false;
    let prenom = document.getElementById('prenom').value.trim();
    let nom    = document.getElementById('nom').value.trim();
    let login  = document.getElementById('login').value.trim();
    
    document.querySelectorAll('.err-msg').forEach(el => el.style.display = 'none');

    if (prenom === '') { document.getElementById('err-prenom').style.display = 'block'; hasError = true; }
    if (nom === '')    { document.getElementById('err-nom').style.display = 'block'; hasError = true; }
    if (login === '')  { document.getElementById('err-login').style.display = 'block'; hasError = true; }

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
