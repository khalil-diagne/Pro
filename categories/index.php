<?php
require_once '../config.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] === 'visiteur') {
    header('Location: ' . BASE_URL . 'connexion.php');
    exit;
}

$cats = $pdo->query("SELECT id, nom, created_at FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// Gestion des messages de retour
$msg = $_GET['msg'] ?? '';
$erreur = $_GET['err'] ?? '';
?>
<?php require_once '../entete.php'; ?>
<?php require_once '../menu.php'; ?>

<main class="wrapper" style="max-width: 800px;">
    <div class="section-head" style="display:flex; justify-content:space-between; align-items:center;">
        <h2 style="margin:0;">Gestion des catégories</h2>
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
                <th style="padding:12px; text-align:left; font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Nom</th>
                <th style="padding:12px; text-align:center; font-family:var(--font-mono); font-size:0.8rem; text-transform:uppercase;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($cats)): ?>
                <tr>
                    <td colspan="3" style="padding:20px; text-align:center;">Aucune catégorie existante.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($cats as $c): ?>
                <tr style="border-bottom:1px solid var(--paper-rule);">
                    <td style="padding:12px; font-family:var(--font-mono); font-size:0.9rem;"><?= $c['id'] ?></td>
                    <td style="padding:12px; font-weight:bold; color:var(--ink);"><?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td style="padding:12px; text-align:center;">
                        <a href="modifier.php?id=<?= $c['id'] ?>" style="color:var(--accent2); margin-right:15px; font-size:0.9rem;">Modifier</a>
                        <a href="supprimer.php?id=<?= $c['id'] ?>" style="color:var(--accent); font-size:0.9rem;" onclick="return confirm('Confirmer la suppression de cette catégorie ?\n\nNB: Si des articles sont liés à cette catégorie, ils empêcheront sa suppression.');">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<footer class="footer">
    &copy; <?= date('Y') ?> La Tribune &mdash; Tous droits réservés
</footer>
</body>
</html>
