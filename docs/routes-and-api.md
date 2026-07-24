# Routes et API

Les routes sont des attributs PHP des contrôleurs. La liste réellement chargée doit toujours être vérifiée avec `php bin/console debug:router`; elle comprend aussi les routes générées par EasyAdmin et les bundles.

## Accès et rôles

| Zone | Authentification attendue |
| --- | --- |
| `/pro` | `ROLE_AGENCE`, à l'exclusion des rôles admin et visiteur dans la règle actuelle. |
| `/user` | `ROLE_USER`, à l'exclusion des rôles admin et agence dans la règle actuelle. |
| `/2fa` | Session en cours de double authentification. |
| Toute autre route | Accès public HTTPS selon `security.yaml`. |

Les endpoints API de facturation portent en plus l'attribut `IsGranted('ROLE_AGENCE')` et vérifient leur jeton CSRF dans le code.

## Routes applicatives par module

| Module | Routes principales | Contrôleur |
| --- | --- | --- |
| Public | `/`, `/fr`, `/filter`, `/public/search`, `/public/search/{searchToken}` | `HomeController`, `ResultFilterController`, `SearchController` |
| Détail | `/agency/{slug}`, `/public/detail/bien/{slug}` | `DetailAgenceController`, `DetailBienController` |
| Géographie | `/geo/autocomplete/pays`, `/villes`, `/quartiers` | `GeoAutocompleteController` |
| Visiteur | `/login`, `/signup`, `/signup/verify`, `/signup/profile`, `/visiteur/dashboard`, `/favoris/property/{id}/toggle` | contrôleurs `Authentification/Visiteurs` et `Dashboard/Visiteur` |
| Agence | `/pro/login`, `/pro/signup`, `/pro/signup/verify`, `/pro/signup/profile`, `/pro/dashboard` | contrôleurs `Authentification/AgenceImmobiliere` et `Dashboard/AgenceImmobiliere` |
| Gestion de biens | `/mes/biens/`, `/mes/biens/status` | `AgenceImmobiliereMesBiensController` |
| Options | `/immobiliere/options/`, `/immobiliere/options/achat/{id}` | `AgenceImmobiliereOptionsController` |
| Administration | `/admin`, `/admin/user`, `/admin/translation` | EasyAdmin |

Les préfixes localisés produisent les variantes françaises et anglaises listées par le routeur : ne pas coder les URLs en dur dans les templates ou scripts ; utiliser les noms de routes.

## API JSON

| Méthode | Endpoint | Finalité |
| --- | --- | --- |
| `POST` | `/api/agence/billing/setup-intent` | Démarre l'enregistrement sécurisé d'un moyen de paiement Stripe. |
| `POST` | `/api/agence/billing/payment-method/complete` | Finalise et persiste le moyen de paiement. |
| `POST` | `/api/agence/billing/subscription` | Crée l'abonnement associé à un prix de forfait. |
| `POST` | `/dashboard/api/profile` | Met à jour le profil de l'agence. |
| `POST` | `/generate-description-ai` | Génère une description de bien. |
| `POST` | `/api/user/browser-preferences` | Enregistre les préférences navigateur d'un utilisateur. |
| `POST` | `/favoris/property/{id}/toggle` | Ajoute ou retire un favori. |
| `GET` | `/public/search/count` | Retourne le nombre de résultats d'une recherche. |
| `GET` | `/public/search/{searchToken}/map-bounds` | Retourne les limites cartographiques des résultats. |

### Convention de réponse

Les contrôleurs API renvoient `JsonResponse`. Les erreurs de validation ou d'authentification utilisent les statuts HTTP appropriés (`400`, `401`, `403`, `404`, `409`). Les routes de facturation emploient notamment une enveloppe contenant `success` et `message`; les consommateurs doivent traiter le code HTTP avant d'afficher ce message.

## Endpoints de diagnostic

Les routes `/debug/ip-location`, `/debug/ip-location/{ip}`, `/geo/autocomplete/debug-live` et `/test/cloudflare/location` sont utiles en développement. Leur exposition doit être réévaluée avant la mise en production, car elles peuvent révéler des informations de configuration ou de localisation.
