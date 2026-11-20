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
| Public | `/` (redirection), `/{pays}-{langue}/`, `/{pays}-{langue}/public/search`, `/{pays}-{langue}/public/search/{searchToken}` | `HomeController`, `ResultFilterController`, `SearchController` |
| Détail | `/{pays}-{langue}/agency/{slug}`, `/{pays}-{langue}/public/detail/bien/{slug}` | `DetailAgenceController`, `DetailBienController` |
| Géographie | `/{pays}-{langue}/geo/autocomplete/pays`, `/villes`, `/quartiers` | `GeoAutocompleteController` |
| Visiteur | `/login`, `/signup`, `/signup/verify`, `/signup/profile`, `/visiteur/dashboard`, `/favoris/property/{id}/toggle` | contrôleurs `Authentification/Visiteurs` et `Dashboard/Visiteur` |
| Agence | `/pro/login`, `/pro/signup`, `/pro/signup/verify`, `/pro/signup/profile`, `/pro/dashboard` | contrôleurs `Authentification/AgenceImmobiliere` et `Dashboard/AgenceImmobiliere` |
| Gestion de biens | `/mes/biens/`, `/mes/biens/status` | `AgenceImmobiliereMesBiensController` |
| Options | `/immobiliere/options/`, `/immobiliere/options/achat/{id}` | `AgenceImmobiliereOptionsController` |
| Administration | `/admin`, `/admin/user`, `/admin/translation` | EasyAdmin |

Les préfixes localisés produisent les variantes françaises et anglaises listées par le routeur : ne pas coder les URLs en dur dans les templates ou scripts ; utiliser les noms de routes.

### URLs publiques `/{pays}-{langue}/…`

Le front office public est préfixé par `App\Routing\PublicRoute::PREFIX` (`/{_country}-{_locale}`, ex. `/fr-fr/`, `/ca-en/`, `/ma-fr/`), langue et pays étant tous deux dynamiques :

- `_locale` : `fr|en` ; `_country` : code ISO 3166-1 en minuscules, qui doit exister dans la table `pays` (sinon 404).
- `path()`/`generateUrl()` n'ont pas besoin de ces paramètres : `UrlCountrySubscriber` place le pays courant (celui de l'URL, sinon celui détecté par IP, sinon `app.default_country`) dans le contexte du routeur, comme Symfony le fait pour `_locale`.
- `/` redirige (302) vers l'accueil dans la langue et le pays détectés.
- Le pays de l'URL fixe la devise d'affichage (`Pays::$devise`) et filtre les biens de l'accueil (`Property::$codeIsoPays`).

Nouvelle route publique : `#[Route(path: PublicRoute::PREFIX.'/ma-page', name: '…', requirements: PublicRoute::REQUIREMENTS)]`.

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
