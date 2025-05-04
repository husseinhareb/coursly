# Coursly

Ce projet Symfony nécessite quelques étapes avant de pouvoir le lancer en local.

## Prérequis

* PHP (>= 8.0)
* Composer
* Symfony CLI
* Un serveur de base de données (MySQL ou MariaDB)
* phpMyAdmin (ou tout autre outil d’administration)

## Installation

1. **Créer la base de données**

   Avec phpMyAdmin (ou un autre outil), créez une base de données nommée : `coursly`.

2. **Cloner et installer les dépendances**

   ```bash
   cd coursly
   composer install
   ```

3. **Mettre à jour le schéma de la base**

   ```bash
   php bin/console doctrine:schema:update --force
   ```

4. **Charger les fixtures**

   ```bash
   php bin/console doctrine:fixtures:load --append
   ```

5. **Lancer le serveur de développement**

   ```bash
   symfony serve
   ```

Le projet est maintenant accessible à l’adresse indiquée par Symfony CLI (par défaut : [http://127.0.0.1:8000](http://127.0.0.1:8000)).

## Connexion à l'administrateur

Le premier utilisateur créé est l'administrateur :

* **Email :** `admin@coursly.com`
* **Mot de passe :** `admincoursly`

Utilisez ces identifiants pour vous connecter et accéder à la zone d'administration.
