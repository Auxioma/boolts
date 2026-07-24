# Documentation technique Boolts

Cette documentation décrit le code PHP de Boolts à partir de l'application Symfony actuellement configurée. Elle complète le [README racine](../README.md), qui traite surtout de l'installation et de l'utilisation locale.

## Parcours de lecture

| Document | Contenu |
| --- | --- |
| [Architecture PHP](architecture.md) | Organisation des couches, conventions Symfony et flux principaux. |
| [Domaine et persistance](domain.md) | Entités Doctrine, agrégats fonctionnels et règles de stockage. |
| [Routes et API](routes-and-api.md) | Endpoints applicatifs, authentification et contrats JSON. |
| [Développement et exploitation](development.md) | Configuration, commandes de contrôle, services externes et données de démonstration. |

## Périmètre

- Application : Symfony 8 et PHP 8.4 ou supérieur.
- Code documenté : `src/`, `config/`, `migrations/`, `tests/` et les commandes déclarées par Composer.
- Les dépendances tierces sont référencées par leur rôle ; leur comportement détaillé reste documenté dans leur propre documentation.

## Maintenir cette documentation

Toute modification d'un contrôleur, d'une entité, d'un service public ou d'une route doit entraîner la mise à jour de la page concernée. Avant de publier une modification, contrôler les routes réellement chargées avec :

```powershell
php bin/console debug:router
```

et le mapping Doctrine avec :

```powershell
php bin/console doctrine:mapping:info
```
