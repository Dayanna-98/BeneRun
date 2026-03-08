# BeneRun
Application de gestion des bénévoles pour les courses de l'association Béné'Run.

# Démarches à suivre pour l'installation du projet en local
1. Cloner le repository en copiant l'URL. Dans un invite de commande ou le terminal de VS Code se placer ou l'on veut cloner le projet : 
    git clone https://github.com/Dayanna-98/BeneRun.git

2. Après le clonage se placer sur le projet. Dans un invite de commande ou le terminal de VS Code : 
    cd BeneRun

3. Installer les dépendances du projet Composer dans un invite de commande ou le terminal de VS Code :
    composer install

4. Copier le fichier .env dans un invite de commande ou le terminal de VS Code :
    cp .env.example .env

5. Générer la clé Laravel dans un invite de commande ou le terminal de VS Code :
    php artisan key:generate

6. Dans le fichier .env :
    Tout en haut à la ligne : APP_KEY= insérer votre votre key générée
    et dans la partie connexion à la BDD mettre exactement ça : 
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=benerun
    DB_USERNAME=root
    DB_PASSWORD=

7. Lancer le serveur Laravel dans un invite de commande ou le terminal de VS Code : 
    php artisan serve

9. Vérifier que l'application est accessible en cliquant sur l'URL indiquée 

8. Créer la BDD en local dans PHPMyAdmin dans un invite de commande ou le terminal de VS Code :
    php artisan migrate

>>>>>>> 1bce67529fda73e709b46926ff0654232e6cdb5e
