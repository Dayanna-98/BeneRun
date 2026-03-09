# BeneRun
Application de gestion des bénévoles pour les courses de l'association Béné'Run.

# Démarches à suivre pour l'installation du projet en local
Avant toute chose, pour l'installation et l'utilisation du projet il est nécessaire d'avoir sur son poste : 
    - Un compte GitHub pour rejoindre le repository GitHub
    - Visual Studio Code
    - XAMPP ou WAMPP
    - POSTMAN

1. Cloner le repository en copiant l'URL. Dans un invite de commande ou le terminal de Visual Studio Code se placer à l'emplacement où l'on souhaite cloner le projet : 
    git clone https://github.com/Dayanna-98/BeneRun.git

2. Après le clonage ouvrir le projet et se placer sur le projet. Dans un invite de commande ou le terminal de Visual Studio Code : 
    cd BeneRun

3. Installer les dépendances du projet Composer dans un invite de commande ou le terminal de Visual Studio Code :
    composer install

4. Copier le fichier .env dans un invite de commande ou le terminal de Visual Studio Code :
    cp .env.example .env

5. Générer la clé Laravel dans un invite de commande ou le terminal de Visual Studio Code :
    php artisan key:generate

6. Dans le fichier .env :
    Tout en haut à la ligne : APP_KEY= insérer votre votre key générée
    et dans la partie connexion à la BDD mettre : 

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=le port sur quel notre XAMPP OU WAMPP tourne
    DB_DATABASE=benerun
    DB_USERNAME=root
    DB_PASSWORD=

    Cette partie est à configurer selon les paramètres et configuration de chacun. La DB_CONNECTION et DB_DATABASE doivent toutefois être comme indiquées ci-dessus. 

7. Créer la BDD en local dans PHPMyAdmin dans un invite de commande ou le terminal de VS Code :
    php artisan migrate    

8. Lancer le serveur Laravel dans un invite de commande ou le terminal de VS Code : 
    php artisan serve

9. Vérifier que l'application est accessible en cliquant sur l'URL indiquée.

>>>>>>> 1bce67529fda73e709b46926ff0654232e6cdb5e
