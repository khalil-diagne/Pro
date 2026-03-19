<?php
require_once '../config.php';

// Protection Administrateur Exclusive
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'administrateur') {
    header('Location: ' . BASE_URL . 'accueil.php');
    exit;
}

$users = $pdo->query("SELECT id, nom, prenom, login, role, created_at FROM utilisateurs ORDER BY nom, prenom")->fetchAll(PDO::FETCH_ASSOC);

$msg = $_GET['msg'] ?? '';
$erreur = $_GET['err'] ?? '';
?>
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<main class="wrapper" style="max-width: 900px;">
    <div class="section-head" style="display:flex; justify-content:space-between; align-items:center;">
        <h2 style="margin:0;">Gestion des Utilisateurs</h2>
        <a href="ajouter.php" class="btn-page" style="padding:0.4rem 1rem;">Nouveau</a>
    </div>

    <?php if ($msg): ?>
        <div style="background:var(--accent2); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div style="background:var(--accent); color:#fff; padding:10px; margin-bottom:20px; text-align:center;">
            <?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <table style="width:100%; border-collapse:collapse; background:var(--paper-dark); margin-top:1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <thead style="background:var(--ink); color:var(--paper);">
            <tr>
                <th style="padding:12px; text-align:left; font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">ID</th>
                <th style="padding:12px; text-align:left; font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Identité</th>
                <th style="padding:12px; text-align:left; font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Login</th>
                <th style="padding:12px; text-align:left; font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Rôle</th>
                <th style="padding:12px; text-align:center; font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr style="border-bottom:1px solid var(--paper-rule);">
                <td style="padding:12px; font-family:var(--font-mono); font-size:0.9rem;"><?= $u['id'] ?></td>
                <td style="padding:12px; font-weight:bold; color:var(--ink);">
                    <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom'], ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td style="padding:12px;"><?= htmlspecialchars($u['login'], ENT_QUOTES, 'UTF-8') ?></td>
                <td style="padding:12px;">
                    <span class="card__badge" style="background:var(--ink-faint); margin-bottom:0; padding:0.2rem 0.5rem;">
                        <?= htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>
                <td style="padding:12px; text-align:center;">
                    <a href="modifier.php?id=<?= $u['id'] ?>" style="color:var(--accent2); margin-right:15px; font-size:0.9rem;">Modifier</a>
                    
                    <?php if ($u['id'] !== $_SESSION['utilisateur']['id']): ?>
                        <a href="supprimer.php?id=<?= $u['id'] ?>" style="color:var(--accent); font-size:0.9rem;" onclick="return confirm('Attention ! Supprimer cet utilisateur est définitif. Continuer ?');">Supprimer</a>
                    <?php else: ?>
                        <span style="color:var(--ink-faint); font-size:0.9rem; cursor:not-allowed;" title="Vous ne pouvez pas vous supprimer vous-même">Supprimer</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<footer class="footer">
    &copy; <?= date('Y') ?> La Tribune &mdash; Tous droits réservés
</footer>
</body>
</html>
