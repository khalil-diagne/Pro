# Plan d'implémentation : Projet Final Backend (Site d'actualité)

Ce plan détaille la réalisation complète du site d'actualité selon votre cahier des charges.

## 1. Base de données
Création d'un script `database.sql` comprenant les tables :
- `utilisateurs` (id, nom, prenom, login, mot_de_passe, role)
- `categories` (id, nom)
- `articles` (id, titre, contenu_integral, description_courte, date_publication, id_auteur, id_categorie)
(Bonus: ajout d'une colonne `image` dans `articles` pour la fonctionnalité optionnelle)

## 2. Architecture modulaire
Restructuration du code existant ([accueil.php](file:///c:/xampp/htdocs/backend/accueil.php)) selon l'arborescence requise :
- `entete.php` : Balises `<head>`, importation CSS/Polices.
- `menu.php` : Barre de navigation dynamique (selon profil visiteur/éditeur/admin).
- [config.php](file:///c:/xampp/htdocs/backend/config.php) (déjà existant) : Connexion PDO et fonctions métiers sécurisées (protection des routes, échappement, etc.).

## 3. Développement des modules
### Visiteur (Consultation)
- [accueil.php](file:///c:/xampp/htdocs/backend/accueil.php) (déjà bien avancé) : Extraction de l'entête et du menu. Gestion de la pagination en cours.
- `articles/detail.php` : Affichage complet de l'article avec protection contre les failles XSS (`htmlspecialchars`).

### Authentification
- `connexion.php` : Formulaire avec validation JS (côté client) et PHP (côté serveur).
- `deconnexion.php` : Destruction de la session.

### Éditeur (Gestion de contenu)
- Dossier `articles/` : `ajouter.php`, `modifier.php`, `supprimer.php` (incluant l'upload d'image).
- Dossier `categories/` : CRUD (`ajouter.php`, `modifier.php`, `supprimer.php`).
- Utilisation exclusive des requêtes préparées PDO pour l'insertion/modification.

### Administrateur (Gestion des utilisateurs)
- Dossier `utilisateurs/` : CRUD complet pour gérer les rôles (suppression, modification, création).

## 4. Sécurité
- Vérification de l'authentification avec redirection.
- Prévention XSS (systématique via `htmlspecialchars`).
- Prévention injection SQL (PDO préparé uniquement).
- Validation rigoureuse (double validation JS/PHP des formulaires).

## User Review Required
> [!IMPORTANT]
> Souhaitez-vous que je réalise le découpage modulaire de votre page d'accueil (`entete.php` et `menu.php`) immédiatement, ou préférez-vous que je vous génère d'abord le script de la base de données SQL pour avoir des tables propres ?
