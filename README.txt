HPROMODE - Boutique e-commerce luxe en PHP/MySQL

INSTALLATION RAPIDE
1. Placez le dossier "hpromode_project" dans "htdocs" de XAMPP ou dans "www" sous WAMP.
2. Demarrez Apache et MySQL.
3. Importez le fichier "sql/hpromode.sql" dans phpMyAdmin.
4. Verifiez les acces MySQL. Par defaut, le projet utilise :
   - DB_HOST = localhost
   - DB_PORT = 3306
   - DB_NAME = hpromode
   - DB_USER = root
   - DB_PASS = ''
5. Ouvrez dans le navigateur :
   - http://localhost/hpromode_project/
6. Si vous voyez une erreur "Unknown column" ou une base ancienne :
   - ouvrez http://localhost/hpromode_project/install.php

DEPLOIEMENT RENDER
1. Le projet inclut maintenant :
   - Dockerfile
   - render.yaml
   - configuration via variables d environnement
2. Creez un depot GitHub avec ce projet.
3. Sur Render, utilisez "New Blueprint Instance" depuis ce repo.
4. Renseignez les secrets :
   - DB_PASS
   - MYSQL_PASSWORD
   - MYSQL_ROOT_PASSWORD
   - APP_BASE_URL
5. Importez ensuite le schema "sql/hpromode.sql" dans la base MySQL Render si elle est vide.
6. Le fichier "install.php" est desactive en production.

COMPTES DE TEST
Admin
- email : admin@hpromode.test
- mot de passe : admin123

Client
- email : client@hpromode.test
- mot de passe : client123

PAGES PRINCIPALES
- index.php : accueil premium
- shop.php : boutique avec filtres, recherche, tri et pagination
- product-details.php : fiche produit
- cart.php : panier
- checkout.php : validation de commande
- login.php / register.php : espace client
- profile.php : profil et historique
- admin/ : tableau de bord et gestion

FONCTIONNALITES DISPONIBLES
- catalogue de mode luxe responsive
- panier session + synchronisation simple pour client connecte
- systeme de commande complet
- paiement simple extensible
- espace client avec historique
- administration produits, categories, clients, commandes, paiements, promotions, livraisons
- gestion de stock et alertes stock faible

REMARQUES
- Le paiement reste en mode simple/simulation.
- Le projet utilise PHP procedural simple, Bootstrap, JavaScript natif, PDO et MySQL.
- La base SQL contient des donnees de demonstration pour tester rapidement le rendu et l administration.
