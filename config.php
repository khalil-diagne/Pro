<?php
// On démarre la session pour pouvoir stocker des informations (comme l'utilisateur connecté)
session_start();

// On affiche toutes les erreurs PHP pour faciliter le débogage (à enlever en production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ==========================================
// 1. CONNEXION À LA BASE DE DONNÉES
// ==========================================
try {
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'site_actualite';

    // On crée la connexion avec PDO
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);

    // On demande à PDO d'afficher les erreurs sous forme d'exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

}
catch (PDOException $e) {
    // Si la connexion échoue, on arrête tout et on affiche l'erreur
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

// ==========================================
// 2. FONCTIONS PRATIQUES (UTILITAIRES)
// ==========================================

function estConnecte()
{
    if (isset($_SESSION['user_id'])) {
        return true;
    }
    else {
        return false;
    }
}

function estAdmin()
{
    if (estConnecte() == true) {
        if ($_SESSION['role'] === 'admin') {
            return true;
        }
    }
    return false;
}

function protegerRoute()
{
    if (estConnecte() == false) {
        header('Location: connexion.php');
        exit;
    }
}
function protegerRouteAdmin()
{
    if (estAdmin() == false) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Formate une date d'une façon plus lisible (jour/mois/année à heure:minute)
 */
function formaterDate($date)
{
    return date('d/m/Y à H:i', strtotime($date));
}

/**
 * Coupe un texte trop long et rajoute "..." à la fin
 */
function tronquerTexte($texte, $longueur = 150)
{
    // Si le texte est déjà court, on le renvoie tel quel
    if (strlen($texte) <= $longueur) {
        return $texte;
    }

    // Sinon on le coupe
    return substr($texte, 0, $longueur) . '...';
}

/**
 * Prépare un message temporaire (appelé "message flash") pour l'afficher à la prochaine page
 */
function afficherMessage($message, $type = 'info')
{
    $_SESSION['message'] = [
        'texte' => $message,
        'type' => $type
    ];
}

/**
 * Affiche le message temporaire s'il y en a un, puis le supprime pour ne pas le réafficher
 */
function afficherEtSupprimerMessage()
{
    // On vérifie s'il y a un message en session
    if (isset($_SESSION['message'])) {

        // On récupère le texte et le type
        $texteMessage = $_SESSION['message']['texte'];

        // On vérifie le type (info par défaut)
        $typeMessage = 'info';
        if (isset($_SESSION['message']['type'])) {
            $typeMessage = $_SESSION['message']['type'];
        }

        // On supprime le message pour qu'il n'apparaisse qu'une seule fois
        unset($_SESSION['message']);

        // On prépare les couleurs selon le type du message
        $classeCss = 'bg-blue-100 text-blue-800'; // Bleu par défaut (info)

        if ($typeMessage === 'success') {
            $classeCss = 'bg-green-100 text-green-800'; // Vert (succès)
        }
        if ($typeMessage === 'error') {
            $classeCss = 'bg-red-100 text-red-800'; // Rouge (erreur)
        }

        // On affiche le code HTML
        echo "<div class='" . $classeCss . " px-4 py-3 rounded relative mb-4' role='alert'>";
        echo $texteMessage;
        echo "</div>";
    }
}

// Constante racine pour la navigation
define('BASE_URL', '/backend/');
?>
