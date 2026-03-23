<?php
// On charge le fichier principal
require_once '../config.php';

// Protection Exclusive : seuls les administrateurs ont le droit de voir cette page
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'administrateur') {
    header('Location: ' . BASE_URL . 'accueil.php');
    exit;
}

$erreur = '';

// On récupère l'ID de l'utilisateur à modifier depuis l'URL (?id=...)
$id_user = 0;
if (isset($_GET['id'])) {
    if (ctype_digit($_GET['id'])) {
        $id_user = (int) $_GET['id'];
    }
}

// Si l'ID est invalide (égal à 0), on le renvoie à la liste
if ($id_user === 0) {
    header('Location: index.php');
    exit;
}


// Si le formulaire est validé (méthode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // On nettoie toutes les informations saisies
    $nom = "";
    if (isset($_POST['nom'])) {
        $nom = htmlspecialchars(trim($_POST['nom']));
    }

    $prenom = "";
    if (isset($_POST['prenom'])) {
        $prenom = htmlspecialchars(trim($_POST['prenom']));
    }

    $login = "";
    if (isset($_POST['login'])) {
        $login = htmlspecialchars(trim($_POST['login']));
    }

    $mot_de_passe = ""; // Optionnel (l'admin peut vouloir changer le mdp ou le laisser pareil)
    if (isset($_POST['mot_de_passe'])) {
        $mot_de_passe = $_POST['mot_de_passe'];
    }

    $role = "visiteur"; // Rôle par défaut
    if (isset($_POST['role'])) {
        $role = htmlspecialchars(trim($_POST['role']));
    }

    // Sécurité : Un administrateur ne peut pas se retirer ses propres droits par mégarde
    if ($id_user === $_SESSION['utilisateur']['id'] && $role !== 'administrateur') {
        $erreur = "Vous ne pouvez pas retirer vos propres privilèges d'administrateur.";
    } elseif (empty($nom) || empty($prenom) || empty($login)) {
        // On s'assure que les informations clés ne sont pas vides
        $erreur = "Le nom, le prénom et le login sont obligatoires.";
    } elseif ($role !== 'visiteur' && $role !== 'editeur' && $role !== 'administrateur') {
        $erreur = "Rôle invalide attribué.";
    } else {
        // Tout est bon, on met à jour la base de données
        try {
            if (!empty($mot_de_passe)) {
                // Modification AVEC nouveau mot de passe (on le crypte)
                $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                $requete = $pdo->prepare("UPDATE utilisateurs SET nom = :n, prenom = :p, login = :l, role = :r, mot_de_passe = :m WHERE id = :id");
                $requete->execute([
                    ':n' => $nom, 
                    ':p' => $prenom, 
                    ':l' => $login, 
                    ':r' => $role, 
                    ':m' => $hash, 
                    ':id' => $id_user
                ]);
            } else {
                // Modification SANS changer le mot de passe actuel (on ne met pas à jour le champ mot_de_passe)
                $requete = $pdo->prepare("UPDATE utilisateurs SET nom = :n, prenom = :p, login = :l, role = :r WHERE id = :id");
                $requete->execute([
                    ':n' => $nom, 
                    ':p' => $prenom, 
                    ':l' => $login, 
                    ':r' => $role, 
                    ':id' => $id_user
                ]);
            }
            
            // Si l'utilisateur modifie ses propres informations (l'admin lui-même), 
            // il faut mettre à jour la session active pour qu'il voie son nouveau nom en haut à droite
            if ($id_user === $_SESSION['utilisateur']['id']) {
                $_SESSION['utilisateur']['nom'] = $nom;
                $_SESSION['utilisateur']['prenom'] = $prenom;
            }

            // Redirection vers la liste
            header('Location: index.php?msg=' . urlencode('Utilisateur modifié avec succès.'));
            exit;
            
        } catch (PDOException $e) {
            // Si le login est déjà utilisé (code 23000)
            if ($e->getCode() == 23000) {
                $erreur = "Ce login (" . $login . ") est déjà utilisé par un autre compte.";
            } else {
                $erreur = "Erreur PDO : " . $e->getMessage();
            }
        }
    }
}

// -----------------------------------------------------
// AFFICHAGE : On récupère les informations actuelles
// -----------------------------------------------------
$requete_user = $pdo->prepare("SELECT nom, prenom, login, role FROM utilisateurs WHERE id = :id");
$requete_user->execute([':id' => $id_user]);
$user = $requete_user->fetch();

// Si l'utilisateur n'a pas été trouvé en base de données
if (!$user) {
    header('Location: index.php');
    exit;
}

// Pour remplir le formulaire, on utilise ce qui vient d'être posté (en cas d'erreur)
// Sinon on utilise ce qui vient de la base de données
$val_nom = $user['nom'];
if (isset($_POST['nom'])) { $val_nom = $_POST['nom']; }

$val_prenom = $user['prenom'];
if (isset($_POST['prenom'])) { $val_prenom = $_POST['prenom']; }

$val_login = $user['login'];
if (isset($_POST['login'])) { $val_login = $_POST['login']; }

$val_role = $user['role'];
if (isset($_POST['role'])) { $val_role = $_POST['role']; }
?>
<!-- En-tête de la page HTML -->
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<main class="wrapper" style="max-width: 600px;">
    <div class="section-head">
        <h2>Modifier l'utilisateur</h2>
    </div>

    <?php 
    // S'il y a une erreur à afficher
    if ($erreur !== "") { 
    ?>
        <div style="background:var(--accent); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?php echo htmlspecialchars($erreur); ?>
        </div>
    <?php 
    } 
    ?>

    <!-- Formulaire pré-rempli -->
    <form method="post" id="formModifUser" action="modifier.php?id=<?php echo $id_user; ?>" style="display:flex; flex-direction:column; gap:1.5rem; background:var(--paper-dark); padding:2rem; border-radius:4px;">
        
        <div style="display:flex; gap:1rem;">
            
            <div style="flex:1; display:flex; flex-direction:column; gap:.5rem;">
                <label for="prenom" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Prénom</label>
                <input type="text" name="prenom" id="prenom" required value="<?php echo htmlspecialchars($val_prenom); ?>"
                       style="padding: 10px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
                <span class="err-msg" id="err-prenom" style="color:var(--accent); font-size:0.8rem; display:none;">Ce champ est requis</span>
            </div>

            <div style="flex:1; display:flex; flex-direction:column; gap:.5rem;">
                <label for="nom" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Nom</label>
                <input type="text" name="nom" id="nom" required value="<?php echo htmlspecialchars($val_nom); ?>"
                       style="padding: 10px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
                <span class="err-msg" id="err-nom" style="color:var(--accent); font-size:0.8rem; display:none;">Ce champ est requis</span>
            </div>
            
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="login" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Nom d'utilisateur (Login)</label>
            <input type="text" name="login" id="login" required value="<?php echo htmlspecialchars($val_login); ?>"
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
                <?php 
                // On sélectionne le bon rôle en comparant $val_role
                ?>
                <option value="visiteur" <?php if ($val_role === 'visiteur') echo 'selected'; ?>>Visiteur (Consultation uniquement)</option>
                <option value="editeur" <?php if ($val_role === 'editeur') echo 'selected'; ?>>Éditeur (Gère articles & catégories)</option>
                <option value="administrateur" <?php if ($val_role === 'administrateur') echo 'selected'; ?>>Administrateur (Gère accès & tout)</option>
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

<!-- Validation par le navigateur (client) -->
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
    &copy; <?php echo date('Y'); ?> La Tribune &mdash; Tous droits réservés
</footer>
</body>
</html>
