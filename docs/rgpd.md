# 📋 Inventaire RGPD — Entité `User`

Ce document décrit les données personnelles collectées, leur finalité, leur base légale, et les mesures prises pour être conforme au Règlement Général sur la Protection des Données (RGPD) dans l'entité `User` de l'application.

## Données personnelles collectées

| Champ               | Type de donnée           | Finalité du traitement                             | Base légale                 | Stockée | Durée de conservation           |
|---------------------|--------------------------|----------------------------------------------------|-----------------------------|---------|---------------------------------|
| `id`                | Identifiant interne      | Référence technique                                | Intérêt légitime            | ✅       | Tant que le compte est actif    |
| `username`          | Identifiant personnel    | Authentification, affichage dans l'application     | Exécution du contrat        | ✅       | Tant que le compte est actif    |
| `email`             | Adresse email            | Connexion, notifications, récupération de mot de passe | Exécution du contrat    | ✅       | Tant que le compte est actif    |
| `password`          | Hash de mot de passe     | Authentification sécurisée                         | Exécution du contrat        | ✅ (hashé) | Tant que le compte est actif |
| `plainPassword`     | Mot de passe en clair (temporaire) | Traitement temporaire lors de l'inscription ou du changement de mot de passe | Exécution du contrat | ❌ (jamais stocké) | Immédiatement supprimé après traitement |
| `createdAt`         | Date de création         | Journalisation, suivi administratif                | Intérêt légitime            | ✅       | Tant que le compte est actif    |
| `lastConnection`    | Date de dernière connexion | Suivi de l'activité, sécurité                     | Intérêt légitime            | ✅       | À définir selon politique interne |
| `role`              | Donnée de classification | Gestion des autorisations                         | Intérêt légitime            | ✅       | Tant que le compte est actif    |
| `avatarUrl`         | Image facultative        | Personnalisation du profil                         | Consentement ou légitime    | ✅       | Tant que le compte est actif    |
| `active`            | Statut d'activation      | Gestion du compte (actif/inactif)                 | Intérêt légitime            | ✅       | Tant que le compte est actif    |
| `isBanned`          | Statut disciplinaire     | Sécurité, modération                               | Intérêt légitime            | ✅       | Selon politique interne         |

## Mesures RGPD appliquées

- ✅ **Hash sécurisé du mot de passe** via les mécanismes Symfony
- ✅ `plainPassword` non persisté et effacé via `eraseCredentials()`
- ✅ **Création automatique de `createdAt`**
- ✅ **Détection de la dernière connexion** pour analyse d'inactivité
- ✅ **Rôle et bannissement** stockés en base pour gestion d'accès

## Droits de l'utilisateur

L'utilisateur peut à tout moment :

- Demander la **modification** ou **suppression** de ses données
- Accéder à l'ensemble de ses données personnelles
- Demander un **export de ses données** (format JSON ou CSV)
- Supprimer son compte définitivement (**droit à l’effacement**)

## Politique de conservation

- Les comptes sont conservés **tant qu'ils sont actifs**
- Les comptes inactifs peuvent être supprimés après une durée à définir (ex. 2 ans)
- Les données liées à des comptes supprimés peuvent être anonymisées

## Export / Suppression

> 🛠️ Des fonctionnalités d'export ou de suppression doivent être proposées à l'utilisateur via l'interface (ou sur demande au DPO).

---

## À implémenter ou vérifier

- [ ] Afficher la politique de confidentialité (ex. dans le footer)
- [ ] Case à cocher de consentement (si collecte volontaire d'infos ex. avatar ou newsletter)
- [ ] Ajout d’un bouton "Supprimer mon compte"
- [ ] Export de données (commandes Symfony ou API)
- [ ] Anonymisation éventuelle post-suppression (si données liées à d’autres entités)

---

🛡️ **Contact DPO / Responsable du traitement :**

Nom : _(à remplir)_  
Email : _(à remplir)_  
Adresse : _(optionnel)_

---