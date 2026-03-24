<?php
// On charge d'abord le fichier de configuration (connexion BDD, etc.)
require_once 'config.php';

// Si l'utilisateur est déjà connecté, on n'a pas besoin de ce formulaire
if (isset($_SESSION['utilisateur'])) {
    // On le redirige vers l'accueil
    header('Location: index.php');
    exit;
}

// On prépare une variable pour stocker d'éventuels messages d'erreur
$erreur = '';

// On vérifie si le formulaire a été envoyé (si la méthode est POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // On récupère les données envoyées et on les sécurise (htmlspecialchars)
    $login = htmlspecialchars($_POST['login']);
    $mot_de_passe = htmlspecialchars($_POST['mot_de_passe']);

    // 1. On vérifie si les champs sont bien remplis
    if (empty($login)) {
        $erreur = "Le nom d'utilisateur est requis.";
    }
    elseif (empty($mot_de_passe)) {
        $erreur = "Le mot de passe est requis.";
    }
    else {
        // 2. Préparation de la requête pour chercher cet utilisateur dans la base de données
        $requete = $pdo->prepare("SELECT id, nom, prenom, login, mot_de_passe, role FROM utilisateurs WHERE login = :login");
        $requete->bindValue(':login', $login);
        $requete->execute();
        // On récupère l'utilisateur trouvé
        $user = $requete->fetch();

        // 3. On vérifie si l'utilisateur existe ET si le mot de passe correspond au hash dans la BDD
        if ($user) {
            $motDePasseValide = password_verify($mot_de_passe, $user['mot_de_passe']);

            if ($motDePasseValide) {
                // Tout est bon, on régénère l'identifiant de la session (mesure de sécurité)
                session_regenerate_id(true);

                // On enregistre les données de l'utilisateur dans la session
                $_SESSION['utilisateur'] = [
                    'id' => $user['id'],
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom'],
                    'role' => $user['role']
                ];

                // On redirige vers l'accueil
                header('Location: index.php');
                exit;
            }
            else {
                $erreur = "Mot de passe incorrect.";
            }
        }
        else {
            $erreur = "Identifiant non reconnu.";
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

    <?php
if ($erreur !== '') {
?>
        <div style="background:var(--accent); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?php echo htmlspecialchars($erreur); ?>
        </div>
    <?php
}
?>
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
    &copy; <?php echo date('Y'); ?> La Tribune &mdash; Tous droits réservés
</footer>
</body>
</html>
