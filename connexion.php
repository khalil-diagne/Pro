<?php
require_once 'config.php';

// Si l'utilisateur est déjà connecté, redirection vers l'accueil
if (isset($_SESSION['utilisateur'])) {
    header('Location: accueil.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Échappement préventif et trim
    $login = trim($_POST['login'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // Validation PHP côté serveur
    if (empty($login) || empty($mot_de_passe)) {
        $erreur = "Tous les champs sont requis.";
    } else {
        // Prévention injection SQL : utilisation de requête préparée PDO
        $stmt = $pdo->prepare("SELECT id, nom, prenom, login, mot_de_passe, role FROM utilisateurs WHERE login = :login");
        $stmt->bindValue(':login', $login, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification du mot de passe avec password_verify
        if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            // Regénération de l'ID de session pour prévenir la fixation de session (sécurité)
            session_regenerate_id(true);

            // Enregistrement des données en session
            $_SESSION['utilisateur'] = [
                'id'     => $user['id'],
                'nom'    => $user['nom'],
                'prenom' => $user['prenom'],
                'role'   => $user['role']
            ];
            
            header('Location: accueil.php');
            exit;
        } else {
            $erreur = "Identifiants incorrects.";
        }
    }
}
?>
<?php require_once 'entete.php'; ?>
<?php require_once 'menu.php'; ?>

<main class="wrapper" style="max-width: 500px;">
    <div class="section-head">
        <h2>Connexion</h2>
    </div>

    <?php if ($erreur): ?>
        <div style="background:var(--accent); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="post" action="connexion.php" id="formConnexion" style="display:flex; flex-direction:column; gap:1.5rem;">
        
        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="login" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase; letter-spacing:1px;">Nom d'utilisateur</label>
            <input type="text" name="login" id="login" required 
                   style="padding: 10px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
            <span id="err-login" style="color:var(--accent); font-size:0.8rem; display:none;">Ce champ est requis</span>
        </div>

        <div style="display:flex; flex-direction:column; gap:.5rem;">
            <label for="mot_de_passe" style="font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase; letter-spacing:1px;">Mot de passe</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" required 
                   style="padding: 10px; font-family:var(--font-body); font-size:1rem; border:1px solid var(--paper-rule); border-radius:3px;">
            <span id="err-mdp" style="color:var(--accent); font-size:0.8rem; display:none;">Ce champ est requis</span>
        </div>

        <button type="submit" class="btn-page" style="justify-content:center; margin-top:10px;">
            Se connecter
        </button>

    </form>
</main>

<script>
// Validation JavaScript côté client détaillée
document.getElementById('formConnexion').addEventListener('submit', function(e) {
    let hasError = false;
    let login = document.getElementById('login').value.trim();
    let mdp = document.getElementById('mot_de_passe').value.trim();
    
    // Réinitialisation des messages d'erreur
    document.getElementById('err-login').style.display = 'none';
    document.getElementById('err-mdp').style.display = 'none';

    if (login === '') {
        document.getElementById('err-login').style.display = 'block';
        hasError = true;
    }

    if (mdp === '') {
        document.getElementById('err-mdp').style.display = 'block';
        hasError = true;
    }

    if (hasError) {
        // Empêche la soumission du formulaire vers le script PHP si erreur
        e.preventDefault(); 
    }
});
</script>

<footer class="footer">
    &copy; <?= date('Y') ?> La Tribune &mdash; Tous droits réservés
</footer>
</body>
</html>
