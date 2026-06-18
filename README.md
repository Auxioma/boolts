# Boolts

Plateforme web immobilière développée avec **Symfony 8** pour la gestion, la publication et la recherche de biens immobiliers.

Le projet contient une partie publique pour les visiteurs, une partie professionnelle pour les agences immobilières, un système d’authentification différencié, une recherche géographique via Mapbox, un parcours multi-étapes de création d’annonce, la gestion des favoris, des formulaires de contact, l’upload d’images et l’optimisation des visuels.

---

## Sommaire

- [Présentation](#présentation)
- [Fonctionnalités principales](#fonctionnalités-principales)
- [Stack technique](#stack-technique)
- [Architecture du projet](#architecture-du-projet)
- [Prérequis](#prérequis)
- [Installation locale](#installation-locale)
- [Configuration des variables d’environnement](#configuration-des-variables-denvironnement)
- [Base de données](#base-de-données)
- [Assets front-end](#assets-front-end)
- [Lancer le projet](#lancer-le-projet)
- [Commandes utiles](#commandes-utiles)
- [Authentification et sécurité](#authentification-et-sécurité)
- [Recherche Mapbox](#recherche-mapbox)
- [Gestion des biens immobiliers](#gestion-des-biens-immobiliers)
- [Upload et traitement des images](#upload-et-traitement-des-images)
- [Fixtures](#fixtures)
- [Qualité de code](#qualité-de-code)
- [Tests](#tests)
- [Déploiement production](#déploiement-production)
- [État actuel et points de vigilance](#état-actuel-et-points-de-vigilance)
- [Licence](#licence)

---

## Présentation

**Boolts** est une application immobilière permettant à des agences de créer et gérer leurs annonces, et aux visiteurs de rechercher des biens selon différents critères géographiques et immobiliers.

Objectifs principaux :

- publier des annonces immobilières ;
- gérer un espace professionnel agence ;
- gérer un espace visiteur ;
- proposer une recherche géographique internationale ;
- afficher des fiches biens détaillées ;
- permettre la prise de contact avec une agence ;
- gérer les favoris visiteurs ;
- préparer une base solide pour une marketplace immobilière multilingue.

---

## Fonctionnalités principales

### Partie publique

- Page d’accueil.
- Recherche de biens immobiliers.
- Recherche par pays, ville ou code postal.
- Autocomplétion géographique via Mapbox.
- Affichage des biens.
- Page détail d’un bien.
- Affichage des biens similaires.
- Formulaire de contact lié à une agence.
- Gestion des favoris.

### Partie agence immobilière

- Dashboard agence.
- Création d’un bien immobilier en plusieurs étapes.
- Gestion des informations principales du bien.
- Gestion de l’adresse et des données Mapbox.
- Gestion des caractéristiques.
- Gestion du bilan énergétique.
- Upload et ordre des photos.
- Gestion du titre, de la description et du prix.
- Gestion des paramètres, factures, aide, options et messagerie.

### Partie visiteur

- Inscription visiteur.
- Connexion visiteur.
- Dashboard visiteur.
- Profil visiteur.
- Favoris.

### Authentification

- Authentification visiteur.
- Authentification agence immobilière.
- Connexion via Google OAuth.
- Double authentification par email.
- Remember me.
- Limitation des tentatives de connexion.

---

## Stack technique

### Back-end

- PHP `>= 8.4`
- Symfony `8.0`
- Doctrine ORM
- Doctrine Migrations
- Symfony Form
- Symfony Security
- Symfony Mailer
- Symfony Validator
- Symfony Translation
- Symfony Serializer
- Twig
- EasyAdmin
- KnpPaginator
- Scheb Two-Factor Bundle
- Reset Password Bundle
- VichUploaderBundle
- LiipImagineBundle
- Gedmo Doctrine Extensions

### Front-end

- Symfony AssetMapper
- Importmap
- Stimulus
- Turbo
- Bootstrap `5.3`
- Splide
- Mmenu
- Intl Tel Input
- Chart.js
- CSS personnalisé

### Services externes

- Mapbox Geocoding API
- Google OAuth
- Service SMTP via Symfony Mailer

---

## Architecture du projet

Structure principale :

```txt
boolts/
├── assets/
│   ├── app.js
│   ├── controllers/
│   ├── fonts/
│   └── styles/
├── bin/
│   └── console
├── config/
│   ├── packages/
│   ├── routes/
│   ├── routes.yaml
│   └── services.yaml
├── migrations/
├── public/
│   ├── images/
│   └── index.php
├── src/
│   ├── Controller/
│   │   ├── Authentification/
│   │   ├── Dashboard/
│   │   └── Public/
│   ├── DataFixtures/
│   ├── Entity/
│   ├── Form/
│   ├── Mailer/
│   ├── Repository/
│   ├── Security/
│   ├── Service/
│   └── Vich/
├── templates/
│   ├── components/
│   ├── dashboard/
│   ├── email/
│   └── public/
├── tests/
├── translations/
├── composer.json
├── importmap.php
├── phpstan.dist.neon
├── phpunit.dist.xml
└── .php-cs-fixer.dist.php
```

---

## Prérequis

Avant d’installer le projet, vérifier que l’environnement possède :

- PHP `8.4` ou supérieur ;
- Composer ;
- Symfony CLI, recommandé en développement ;
- une base de données compatible Doctrine ;
- l’extension PHP `imagick`, utilisée par LiipImagine ;
- un serveur SMTP ou un service email ;
- un compte Mapbox ;
- un projet Google OAuth si la connexion Google est utilisée.

Vérification rapide :

```bash
php -v
composer -V
symfony -v
```

---

## Installation locale

Cloner le projet :

```bash
git clone https://github.com/Auxioma/boolts.git
cd boolts
```

Installer les dépendances PHP :

```bash
composer install
```

Créer le fichier d’environnement local :

```bash
cp .env .env.local
```

Créer le secret d’application si nécessaire :

```bash
php -r "echo bin2hex(random_bytes(32)).PHP_EOL;"
```

Puis placer la valeur dans `.env.local`.

---

## Configuration des variables d’environnement

Exemple de configuration `.env.local` :

```dotenv
APP_ENV=dev
APP_DEBUG=1
APP_SECRET=change_me

DATABASE_URL="mysql://test:@127.0.0.1:3306/test?serverVersion=8.0&charset=utf8mb4"

MAILER_DSN=smtp://user:password@smtp.example.com:587
MAILER_FROM_EMAIL=test@test.com
MAILER_FROM_NAME=test

MAPBOX_PUBLIC_TOKEN=pk.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
DEFAULT_URI=http://127.0.0.1:8000
```

### Variables importantes

| Variable | Rôle |
|---|---|
| `APP_ENV` | Environnement Symfony : `dev`, `test`, `prod` |
| `APP_DEBUG` | Active ou désactive le mode debug |
| `APP_SECRET` | Secret Symfony |
| `DATABASE_URL` | Connexion à la base de données |
| `MAILER_DSN` | Configuration SMTP |
| `MAILER_FROM_EMAIL` | Adresse email expéditrice |
| `MAILER_FROM_NAME` | Nom expéditeur |
| `MAPBOX_PUBLIC_TOKEN` | Token public Mapbox |
| `DEFAULT_URI` | URL utilisée hors contexte HTTP |

---

## Base de données

Créer la base de données :

```bash
php bin/console doctrine:database:create
```

Exécuter les migrations :

```bash
php bin/console doctrine:migrations:migrate
```

Charger les fixtures :

```bash
php bin/console doctrine:fixtures:load
```

Recréer complètement la base en développement :

```bash
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

---

## Assets front-end

Le projet utilise Symfony AssetMapper et Importmap.

Installer les dépendances front gérées par Importmap :

```bash
php bin/console importmap:install
```

Lister les assets connus par AssetMapper :

```bash
php bin/console debug:asset-map
```

Compiler les assets pour la production :

```bash
php bin/console asset-map:compile
```

Point d’entrée principal :

```txt
assets/app.js
```

Ce fichier importe notamment :

- Bootstrap ;
- Bootstrap CSS ;
- Lucide icons ;
- Mmenu ;
- Splide CSS ;
- le CSS principal ;
- Stimulus ;
- Intl Tel Input CSS.

---

## Lancer le projet

Avec Symfony CLI :

```bash
symfony server:start
```

Ou avec PHP directement :

```bash
php -S 127.0.0.1:8000 -t public
```

Accéder ensuite au projet :

```txt
http://127.0.0.1:8000
```

---

## Commandes utiles

### Symfony

```bash
php bin/console cache:clear
php bin/console debug:router
php bin/console debug:container
php bin/console debug:autowiring
php bin/console about
```

### Doctrine

```bash
php bin/console doctrine:schema:validate
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### Traductions

```bash
php bin/console translation:extract fr --force
php bin/console translation:extract en --force
```

### Assets

```bash
php bin/console importmap:install
php bin/console debug:asset-map
php bin/console asset-map:compile
```

---

## Authentification et sécurité

Le projet possède plusieurs systèmes d’authentification :

- authentification visiteur ;
- authentification agence immobilière ;
- authentification Google ;
- double authentification par email.

Les utilisateurs sont stockés dans l’entité `User`.

L’application utilise les rôles suivants :

| Rôle | Description |
|---|---|
| `ROLE_ADMIN` | Administration |
| `ROLE_AGENCE` | Espace professionnel agence |
| `ROLE_USER` | Espace visiteur |

Les routes professionnelles sont protégées par le rôle `ROLE_AGENCE`.

Les routes visiteurs sont protégées par le rôle `ROLE_USER`.

La double authentification est disponible via :

```txt
/2fa
/2fa_check
```

---

## Recherche Mapbox

Le projet utilise Mapbox pour l’autocomplétion géographique.

Le contrôleur Stimulus principal de la recherche publique est :

```txt
assets/controllers/home-bt-mapbox-search_controller.js
```

La recherche accepte notamment :

- pays ;
- ville ;
- localité ;
- code postal.

Types Mapbox utilisés :

```txt
country,place,locality,postcode
```

Données stockées après sélection :

- valeur affichée ;
- Mapbox ID ;
- type de résultat ;
- pays ;
- code pays ;
- région ;
- ville ;
- code postal ;
- latitude ;
- longitude ;
- adresse complète ;
- langue ;
- JSON complet de la sélection.

Le formulaire associé est :

```txt
src/Form/SearchBar/FilterCityCountryType.php
```

L’objet de transport des données est :

```txt
src/Entity/SearchBar/FilterCityCountry.php
```

---

## Gestion des biens immobiliers

L’entité principale est :

```txt
src/Entity/Property.php
```

Un bien contient notamment :

- type de bien ;
- type de transaction ;
- adresse ;
- code postal ;
- ville ;
- pays ;
- latitude ;
- longitude ;
- données Mapbox ;
- année de construction ;
- nombre de chambres ;
- salles de bains ;
- surface ;
- DPE ;
- GES ;
- photos ;
- titre ;
- description ;
- prix de vente ;
- loyer hors charges ;
- charges ;
- dépôt de garantie ;
- statut ;
- caractéristiques ;
- favoris ;
- vues ;
- slug numérique unique.

### Parcours de création d’un bien

La création d’un bien côté agence est organisée en 8 étapes :

| Étape | Description |
|---|---|
| 1 | Type de bien |
| 2 | Type de transaction |
| 3 | Adresse |
| 4 | Caractéristiques |
| 5 | Bilan énergétique |
| 6 | Photos |
| 7 | Titre et description |
| 8 | Prix et charges |

Contrôleur principal :

```txt
src/Controller/Dashboard/AgenceImmobiliere/AgenceImmobiliereMesBiensController.php
```

Route principale :

```txt
/mes/biens
```

Le contrôleur conserve la progression en session avec :

- `mes_biens_property_id` ;
- `typeTransaction` ;
- `mes_biens_reached_step`.

### Slug numérique

Les biens utilisent un slug numérique unique généré par :

```txt
src/Service/NumericSlugGenerator.php
```

Par défaut, le slug généré fait 16 chiffres.

---

## Upload et traitement des images

Le projet utilise VichUploaderBundle pour les uploads.

Mappings configurés :

| Mapping | Destination | Usage |
|---|---|---|
| `avatars` | `public/images/avatars` | avatars utilisateurs |
| `biens` | `public/images/biens` | images des biens |

Les images des biens utilisent également un `directory_namer` personnalisé :

```txt
App\Vich\PropertyDirectoryNamer
```

LiipImagine est utilisé pour générer des versions optimisées des images.

Filtres disponibles pour les cartes de biens :

- `property_card_mobile_webp`
- `property_card_tablet_webp`
- `property_card_desktop_webp`
- `property_card_mobile_png`
- `property_card_tablet_png`
- `property_card_desktop_png`

Le driver configuré est :

```txt
imagick
```

---

## Fixtures

Le projet contient plusieurs fixtures pour générer des données de développement.

La fixture des biens génère un volume important de données de test.

Exemples de données générées :

- agences ;
- utilisateurs ;
- biens immobiliers ;
- images ;
- favoris ;
- vues ;
- catégories ;
- caractéristiques ;
- pays ;
- devises ;
- langues ;
- fuseaux horaires.

La fixture `PropertyFixtures` génère des biens immobiliers avec :

- données internationales ;
- localisations Faker ;
- coordonnées GPS ;
- données Mapbox simulées ;
- DPE et GES ;
- prix ou loyer selon le type de transaction ;
- slug numérique unique.

---

## Qualité de code

Le projet contient une configuration PHP CS Fixer :

```txt
.php-cs-fixer.dist.php
```

Lancer PHP CS Fixer :

```bash
vendor/bin/php-cs-fixer fix
```

Tester sans modifier les fichiers :

```bash
vendor/bin/php-cs-fixer fix --dry-run --diff
```

Le projet contient aussi une configuration PHPStan :

```txt
phpstan.dist.neon
```

Lancer PHPStan :

```bash
vendor/bin/phpstan analyse
```

Niveau PHPStan configuré :

```txt
level: 6
```

---

## Tests

Le projet utilise PHPUnit.

Configuration :

```txt
phpunit.dist.xml
```

Lancer les tests :

```bash
vendor/bin/phpunit
```

Ou :

```bash
php bin/phpunit
```

Préparer la base de test si nécessaire :

```bash
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test
```

---

## Déploiement production

### Préparer l’environnement

Variables recommandées :

```dotenv
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=change_me
DATABASE_URL="mysql://user:password@host:3306/database?serverVersion=8.0&charset=utf8mb4"
MAILER_DSN=smtp://user:password@smtp.example.com:587
MAILER_FROM_EMAIL=noreply@boolts.com
MAILER_FROM_NAME=Boolts
MAPBOX_PUBLIC_TOKEN=pk.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
DEFAULT_URI=https://boolts.com
```

### Installer les dépendances

```bash
composer install --no-dev --optimize-autoloader
```

### Migrer la base

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### Compiler les assets

```bash
php bin/console asset-map:compile
```

### Nettoyer le cache

```bash
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

### Permissions à vérifier

Les dossiers suivants doivent être accessibles en écriture par l’application :

```txt
var/
public/images/avatars/
public/images/biens/
```

---

## État actuel et points de vigilance

Cette section liste les points à vérifier avant une mise en production.

### 1. Recherche publique

Le contrôleur de recherche publique contient encore un `dd($filter)`.

Fichier :

```txt
src/Controller/Public/SearchController.php
```

À corriger avant utilisation réelle, sinon la recherche s’arrêtera au dump.

### 2. Méthode `findBySearch`

La méthode `findBySearch()` du repository est typée comme retournant un `array`, mais retourne actuellement un `QueryBuilder`.

Fichier :

```txt
src/Repository/PropertyRepository.php
```

À corriger selon le besoin :

```php
return $qb->getQuery()->getResult();
```

ou changer le type de retour si la pagination doit exploiter le QueryBuilder.

### 3. Page détail bien

Le contrôleur de détail bien utilise actuellement des variables qui doivent être injectées ou récupérées correctement :

- `$user`
- `$entityManager`

Fichier :

```txt
src/Controller/Public/DetailBienController.php
```

Ces variables doivent être corrigées avant d’activer le formulaire de contact.

### 4. Secret d’application

Ne jamais versionner de vrais secrets.

À vérifier :

- `.env.local` ne doit jamais être commité ;
- `APP_SECRET` doit être propre à chaque environnement ;
- tout secret déjà exposé doit être régénéré.

### 5. Données Mapbox

Le token Mapbox doit rester un token public limité par domaine si le projet est en production.

À vérifier côté Mapbox :

- restriction par domaine ;
- quotas ;
- facturation ;
- limites API.

### 6. HTTPS

La configuration sécurité force l’accès public en HTTPS.

En local, selon la configuration utilisée, il peut être nécessaire d’utiliser Symfony CLI avec certificat local ou d’adapter temporairement la configuration de développement.

---

## Bonnes pratiques recommandées

### Branches Git

Exemple d’organisation simple :

```txt
master
develop
feature/nom-fonctionnalite
fix/nom-correction
```

### Commits

Format conseillé :

```txt
feat: ajout recherche Mapbox
fix: correction formulaire contact bien
refactor: amélioration repository Property
docs: ajout README
style: correction CSS carte bien
test: ajout tests favoris
```

### Avant chaque commit

```bash
vendor/bin/php-cs-fixer fix --dry-run --diff
vendor/bin/phpstan analyse
vendor/bin/phpunit
```

---

## Licence

Projet propriétaire.

Copyright © 2026 Boolts.

Ce projet est développé par Auxioma Web Agency pour Pastelit Co.

Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
