# Songbook 🎵

[![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-000000?style=flat&logo=symfony&logoColor=white)](https://symfony.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)](https://developer.mozilla.org/fr/docs/Web/JavaScript)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=flat&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

## Table des matières

- [Fonctionnalités principales](#-fonctionnalités-principales)
- [Résumé technique](#-résumé-technique)
- [Schéma de la base de données](#schéma-de-la-base-de-données)
- [Prochaines améliorations](#-prochaines-améliorations)
- [Aperçu / Screenshots](#-aperçu--screenshots)
- [Technologies utilisées](#technologies-utilisées)
- [Installation](#-installation)
- [Crédits](#-crédits)

**Songbook** est une application musicale interactive créée comme projet de fin d’études, permettant aux musiciens de créer, organiser et partager leurs partitions au format ChordPro.
C’est un projet complet backend + frontend qui illustre mes compétences en PHP/Symfony et JavaScript.

Le projet couvre l’ensemble du flux d’une application moderne : backend Symfony, gestion de rôles, upload de fichiers, interface JavaScript dynamique, système de partitions et outils d’administration.

---

## 🚀 Fonctionnalités principales

- Gestion des partitions au format **ChordPro**, pour un rendu clair et compatible avec les outils de musique
- Création et gestion de songbooks
- Gestion des accords et des tablatures
- Organisation de setlists pour les concerts ou répétitions
- Interface interactive et responsive

## 🔧 Résumé technique

- Architecture MVC Symfony avec services dédiés

- Authentification + gestion des rôles (User, Moderator, Admin)

- CRUD complet : partitions, songbooks, setlists

- Upload et parsing de fichiers ChordPro

- Génération dynamique du rendu des partitions

- Export complet des données utilisateur (conformité RGPD)

- Suppression de compte sécurisée (transfert préalable du rôle Admin)

- Système d’export, import et mise à jour du profil

- Utilisation de Doctrine (entités, relations, migrations, fixtures)

- Validation via les FormTypes Symfony

- Sécurisation via hCaptcha

- Gestion des assets avec Webpack Encore

- Outils d'administration (statistiques + gestion globale des partitions)

## 🗃️ Schéma de la base de données

```mermaid
erDiagram

    USER ||--o{ CHORDSHEET : "creates"
    USER ||--o{ SONGBOOK : "owns"
    USER }o--|| ROLE : "has"

    SONGBOOK ||--o{ SONGBOOK_CHORDSHEET : "links"
    CHORDSHEET ||--o{ SONGBOOK_CHORDSHEET : "linked in"

    %% Tables

    USER {
        int id PK
        string email
        string password
        datetime created_at
    }

    ROLE {
        int id PK
        string name
        string description
    }

    CHORDSHEET {
        int id PK
        int user_id FK
        string title
        longtext content
        datetime created_at
        boolean is_public
        datetime published_at
    }

    SONGBOOK {
        int id PK
        int user_id FK
        string title
        string description
        datetime created_at
    }

    SONGBOOK_CHORDSHEET {
        int id PK
        int songbook_id FK
        int chordsheet_id FK
        datetime added_at
        int position
    }

    GENRE {
        int id PK
        string name
        string description
    }

    TONALITY {
        int id PK
        string name
        string description
        string type
    }

    %% (Optional table used by Symfony Messenger)
    MESSENGER_MESSAGES {
        bigint id PK
        longtext body
        longtext headers
        string queue_name
        datetime created_at
        datetime available_at
        datetime delivered_at
    }
```
💡 Les tables `GENRE` et `TONALITY` sont déjà définies dans la base de données mais ne sont pas encore utilisées dans l’application. Elles serviront de fondation à des fonctionnalités futures (classement, filtres, outils musicaux).
## 🌱 Prochaines améliorations

- Intégration d’éléments multimédias (audio, images)

---

## 📷 Aperçu / Screenshots

### Page d’accueil (sans connexion)

![Page d'accueil](assets/images/screenshots/screenshot-1.png)
_Page d'accueil, accessible sans connexion._

### Page d'inscription

![Page d'inscription'](assets/images/screenshots/screenshot-2.png)
_Formulaire d'inscription, sécurisé avec hCaptcha._

### Création d'une partition

![Création d'une partition](assets/images/screenshots/screenshot-3.png)
_Aperçu de la création d'une partition avec l'écriture au format ChordPro à gauche et le rendu à droite. Possibilité d'importer directement un fichier en local._

### Bibliothèque

![Bibliothèque](assets/images/screenshots/screenshot-4.png)
_Aperçu de la bibliothèque permettant à tout utilisateur inscrit de consulter ou d'ajouter les partitions des autres utilisateurs. Un visiteur ne pourra que consulter les partitions._

### Profil

![Profil](assets/images/screenshots/screenshot-5.png)
_Aperçu de la page de profil permettant la mise à jour et l'export de données. La suppression de l'admin est ici impossible sans transmission préalable du rôle._

### Gestion des partitions par l'admin

![Gestion des partitions](assets/images/screenshots/screenshot-6.png)
_Aperçu de la page de gestion des partitions, avec statistiques générales_

## 🛠️ Technologies utilisées

- **Backend** : PHP / Symfony
- **Frontend** : JavaScript
- **Styles** : Bootstrap, SCSS
- **Base de données** : MySQL
- **Autres outils** : Composer, Webpack Encore, GitHub

---

## 💻 Installation

### 1️⃣ Cloner le repository

```bash
git clone https://github.com/gaetan-denis/songbook.git
```

### 2️⃣ Installer les dépendances PHP

```bash
composer install
```

### 3️⃣ Installer les dépendances JavaScript

```bash
npm install
```

### 4️⃣ Lancer le serveur de développement

```bash
npm run dev
```

### 5️⃣ Configurer la base de données

- Ouvrir le fichier .env et adapter les lignes 26 à 29 selon vos paramètres

- Créer la base de données :

```bash
php bin/console doctrine:database:create
```

- Exécuter les migrations :

```bash
php bin/console doctrine:migrations:migrate
```

- Charger les fixtures :

```bash
php bin/console doctrine:fixtures:load
```

### 6️⃣ Démarrer le serveur Symfony

```bash
symfony server:start
```

## 🎨 Crédits

### Fichier de réinitialisation SCSS

- Licence : MIT

- Author : Fraser Boag

- Repository : [sass-reset](https://github.com/fraserboag/sass-reset)

### Image de fond

- Two Grayscale Acoustic Guitars

- Image libre de droit (CCO)

- Lien : [Pexels](https://www.pexels.com/photo/two-grayscale-acoustic-guitars-290660/)
