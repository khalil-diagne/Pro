<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connexion DB (même config que les autres fichiers)
try {
    $dbHost = 'localhost'; // ca dependra du deploiement
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'site_actualite';

    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur BDD: ' . $e->getMessage());
}

// --- Fonctions utilitaires ---

/**
 * Vérifie si l'utilisateur est connecté
 */
function estConnecte(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur est admin
 */
function estAdmin(): bool
{
    return estConnecte() && $_SESSION['role'] === 'admin';
}

/**
 * Redirige vers la page de connexion si non connecté
 */
function protegerRoute(): void
{
    if (!estConnecte()) {
        header('Location: connexion.php');
        exit;
    }
}

/**
 * Redirige vers l'accueil si non connecté
 */
function protegerRouteAdmin(): void
{
    if (!estAdmin()) {
        header('Location: accueil.php');
        exit;
    }
}

/**
 * Formate une date
 */
function formaterDate(string $date): string
{
    return date('d/m/Y à H:i', strtotime($date));
}

/**
 * Tronque un texte
 */
function tronquerTexte(string $texte, int $longueur = 150): string
{
    if (strlen($texte) <= $longueur) {
        return $texte;
    }
    return substr($texte, 0, $longueur) . '...';
}

/**
 * Affiche un message flash
 */
function afficherMessage(string $message, string $type = 'info'): void
{
    $_SESSION['message'] = [
        'texte' => $message,
        'type' => $type
    ];
}

/**
 * Affiche et supprime le message flash
 */
function afficherEtSupprimerMessage(): void
{
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);

        $classes = [
            'info' => 'bg-blue-100 text-blue-800',
            'success' => 'bg-green-100 text-green-800',
            'error' => 'bg-red-100 text-red-800'
        ];

        $classe = $classes[$message['type'] ?? 'info'] ?? $classes['info'];

        echo "<div class='{$classe} px-4 py-3 rounded relative mb-4' role='alert'>
                {$message['texte']}
              </div>";
    }
}

// Constante racine pour la navigation
define('BASE_URL', '/backend/');
?>
