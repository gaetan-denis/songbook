# Songbook

## Manuel d'installation

1. À partir de la page du [repository](https://github.com/gaetan-denis/songbook), cliquez sur `<> CODE` puis choisissez une méthode pour cloner le [repository](https://github.com/gaetan-denis/songbook) en local (pour l'exemple nous choisirons ici `https://github.com/gaetan-denis/songbook.git`).

2. En local, ouvrez ensuite le terminal de votre choix, rendez-vous à l'emplacement où vous souhaitez télécharger l'application et exécuter ensuite la commande :
    ```bash
    git clone https://github.com/gaetan-denis/songbook.git
    ```
3. Téléchargez et installez ensuite les dépendances dans le dossier `vendor`en exécutant la commande :
    ```bash 
    composer install
    ``` 

4. Installez ensuite les dépendances JavaScript nécessaires à Webpack Encore :
    ```bash
    npm install
    ```

5. Lancez ensuite le serveur de développement en exécutant la commande :
    ```bash
    npm run dev
    ```

6. Ouvrez ensuite l'application avec l'IDE de votre choix puis configurer le fichier `.env`. Portez une attention toute particulière aux lignes `26` jusque `29` permettant la configuration de votre base de donnée. Pour ce faire, décommentez la ligne concernée et modifiez-la ensuite en fonction de vos paramètres personnels.
7. Démarrez ensuite le serveur en effectuant la commande :
    ```bash
    symfony server:start
    ```
## Installation de la base de donnée

1. Création de la base de donnée
   ```bash
   php bin/console doctrine:database:create
   ```
2. Exécution des migrations
   ```bash
   php bin/console doctrine:migrations:migrate
   ```
3. Chargement des fixtures
   ```bash
   php bin/console doctrine:fixtures:load
   ```
## Crédits :

### Fichier de réinitialisation SCSS
- **Licence :** MIT
- **Author :** Fraser Boag
- **Repository :** https://github.com/fraserboag/sass-reset

### Image de fond :
- **Artiste :** Charl Durand
- **Lien :** https://www.pexels.com/fr-fr/photo/main-musicien-sepia-jouer-de-la-musique-15320380/