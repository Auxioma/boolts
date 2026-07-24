# Développement et exploitation

## Variables d'environnement

| Variable | Usage |
| --- | --- |
| `DATABASE_URL` | Connexion Doctrine. |
| `APP_SECRET` | Secret Symfony et signature du cookie « remember me ». |
| `MAILER_DSN`, `MAILER_FROM_EMAIL`, `MAILER_FROM_NAME` | Transport et expéditeur des messages. |
| `MAPBOX_PUBLIC_TOKEN` | Géocodage et traduction d'adresses. |
| `STRIPE_SECRET_KEY`, `STRIPE_PUBLIC_KEY` | Paiements et souscriptions Stripe. |
| `DEFAULT_URI` | Génération d'URL hors requête HTTP. |

Ne jamais inscrire une valeur réelle dans les fichiers versionnés. Utiliser `.env.local` en local et les secrets/variables du serveur en production.

## Commandes de contrôle

```powershell
# Syntaxe PHP et qualité statique
Get-ChildItem src -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
vendor/bin/php-cs-fixer fix --dry-run --diff
vendor/bin/phpstan analyse
vendor/bin/phpunit

# Symfony et Doctrine
php bin/console lint:yaml config
php bin/console lint:twig templates
php bin/console debug:router
php bin/console doctrine:mapping:info
php bin/console doctrine:schema:validate
```

## Base de données et fixtures

Les migrations se trouvent dans `migrations/`. Les fixtures sont regroupées sous `src/DataFixtures`, avec des sous-ensembles pour l'authentification, les emails et les données de référence.

```powershell
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

La commande de fixtures peut purger la base ciblée : vérifier `DATABASE_URL` avant de l'exécuter. Pour une vérification sans toucher à une base locale existante, pointer temporairement `DATABASE_URL` vers une base SQLite isolée.

## Fichiers et médias

VichUploader est utilisé pour les avatars et les images de biens. `PropertyDirectoryNamer` crée l'organisation des répertoires par bien. LiipImagine génère les variations de cartes. Les répertoires de médias doivent être inscriptibles par le processus PHP en production.

## Intégrations externes

| Intégration | Composant PHP |
| --- | --- |
| Stripe | `StripeClient`, `PaymentMethodController`, `SubscriptionController` |
| Mapbox | `MapboxAddressTranslator` et contrôleurs de géographie |
| Google OAuth | `GoogleAuthenticator`, `GoogleController` |
| Email | Symfony Mailer, `ContactMailer`, `TwoFactorEmailMailer` |
| GeoIP / Cloudflare | Services de localisation IP et contrôleurs de diagnostic |

## Sécurité de production

- Le firewall principal active le throttling : cinq tentatives pendant quinze minutes.
- Le cookie « remember me » est sécurisé, HTTP-only et `SameSite=Lax`; le HTTPS est requis par la règle d'accès par défaut.
- Les appels mutatifs depuis le navigateur doivent conserver leur contrôle CSRF.
- Les webhooks Stripe doivent être authentifiés avant toute modification de données lorsque leur traitement sera exposé.

## Tests et évolution

Le dossier `tests/` contient l'amorce PHPUnit et la configuration auxiliaire PHPStan Doctrine. À chaque ajout de comportement, créer un test PHPUnit ciblé, puis lancer au minimum PHPStan et les tests concernés avant intégration.
