# Logique du forfait gratuit agence

## Contexte

Lorsqu'une agence arrive sur le site, l'objectif metier est de lui permettre de publier un certain nombre d'annonces gratuitement. La question posee est: si un jour l'administrateur ne veut plus proposer cette gratuite, comment le gerer proprement avec les donnees existantes en base de donnees.

Ce document resume l'analyse faite sans changement de code applicatif.

## Etat actuel observe

Le modele de facturation existe deja en base de donnees.

Les entites importantes sont:

- `App\Entity\Billing\SubscriptionPlan`
- `App\Entity\Billing\SubscriptionPlanPrice`
- `App\Entity\Billing\AgencySubscription`
- `App\Entity\Billing\AgencySubscriptionPeriod`
- `App\Entity\Billing\AgencyBillingProfile`
- `App\Entity\Billing\AgencyPaymentMethod`

Le forfait gratuit est represente par un `SubscriptionPlan`.

Dans les fixtures, le plan gratuit existe avec:

- `code = free`
- `name = Gratuit`
- `propertyLimit = 3`
- `includedBoosts = 0`
- `isFree = true`
- `isDefault = true`

Les prix du plan gratuit existent aussi dans `SubscriptionPlanPrice`, avec un montant a `0`.

## Champs importants

Dans `SubscriptionPlan`, les champs qui pilotent la logique metier sont:

- `propertyLimit`: nombre de biens/annonces inclus dans le forfait.
- `includedBoosts`: nombre de boosts inclus.
- `boostDurationDays`: duree d'un boost inclus.
- `isFree`: indique si le plan est gratuit.
- `isDefault`: indique si le plan est le forfait par defaut.
- `isActive`: indique si le plan est disponible.

Dans `AgencySubscription`, il existe des snapshots:

- `propertyLimitSnapshot`
- `includedBoostsSnapshot`
- `boostDurationDaysSnapshot`
- `amountSnapshotMinor`
- `currencySnapshot`

Ces snapshots sont importants car ils gardent la valeur du forfait au moment ou l'agence l'a obtenu ou achete.

## Moment des inserts en BDD

Il y a deux inserts differents a ne pas confondre.

### 1. Insert du compte agence

L'agence est inseree en BDD au moment de l'inscription.

Fichier concerne:

`src/Controller/Authentification/AgenceImmobiliere/AgenceImmobiliereRegisterController.php`

La logique cree un `User`, lui donne le role `ROLE_AGENCE`, puis fait un `persist()` et un `flush()`.

A ce moment-la, l'agence existe en BDD, mais l'analyse n'a pas montre de creation automatique d'un `AgencySubscription` gratuit dans cette partie du code.

### 2. Insert de l'annonce

L'annonce est inseree en BDD dans le tunnel "Mes biens", des l'etape 1.

Fichier concerne:

`src/Controller/Dashboard/AgenceImmobiliere/AgenceImmobiliereMesBiensController.php`

La logique cree un `Property`, rattache le bien au `User` agence, puis fait un `persist()` et un `flush()`.

Donc aujourd'hui, le bien est cree tres tot en BDD, meme si l'annonce est encore en brouillon.

## Probleme observe

Le quota gratuit est bien modelise dans les entites et dans la BDD, mais l'analyse n'a pas montre de blocage serveur au moment de creer une annonce.

Autrement dit:

- Le plan gratuit peut afficher "3 biens".
- Le plan peut etre gere depuis l'admin.
- Les abonnements payants copient bien les limites dans les snapshots.
- Mais la creation d'un `Property` ne semble pas encore verifier si l'agence a encore le droit de creer une annonce.

Pour que la gratuite soit vraiment pilotable par l'admin, il faut que la creation d'annonce controle l'abonnement et le quota avant de faire le `persist()` du `Property`.

## Ce que je ferais

### 1. Ne pas supprimer le forfait gratuit

Je ne supprimerais pas le plan `free` de la BDD.

Raison: des anciens abonnements peuvent deja pointer vers ce forfait. Le supprimer casserait l'historique ou les relations.

Je le garderais comme historique, mais je le rendrais indisponible si l'admin ne veut plus proposer de gratuite.

### 2. Desactiver la gratuite cote admin/BDD

Si l'admin ne veut plus proposer d'annonces gratuites aux nouvelles agences:

- passer le plan `free` en `isActive = false`;
- passer ses prix `SubscriptionPlanPrice` en `isActive = false`;
- passer `isDefault = false` sur le plan gratuit;
- choisir un forfait payant comme nouveau forfait par defaut, par exemple `starter`;
- garder les anciens `AgencySubscription` pour conserver l'historique.

### 3. Inserer l'abonnement gratuit au bon moment

Si la gratuite reste active, je ferais l'insert du `AgencySubscription` gratuit au moment ou l'agence devient officiellement utilisable.

Le bon moment est apres la validation du compte agence, par exemple apres la validation OTP ou apres l'inscription confirmee.

A ce moment-la, on cree une ligne `AgencySubscription` avec:

- l'agence concernee;
- le plan gratuit actif par defaut;
- le statut `FREE`;
- `propertyLimitSnapshot` copie depuis `SubscriptionPlan.propertyLimit`;
- `includedBoostsSnapshot` copie depuis `SubscriptionPlan.includedBoosts`;
- `boostDurationDaysSnapshot` copie depuis `SubscriptionPlan.boostDurationDays`;
- `amountSnapshotMinor = 0`;
- la devise si necessaire.

### 4. Controler le quota avant l'insert de l'annonce

Avant de creer un nouveau `Property`, il faut verifier:

- que l'agence a un abonnement valide;
- que l'abonnement est actif ou gratuit;
- que le quota n'est pas atteint;
- que le nombre d'annonces existantes respecte la limite du `propertyLimitSnapshot`.

Si la limite est atteinte, on bloque avant le `persist()` du `Property` et on redirige l'agence vers la page des forfaits.

### 5. Decider la regle sur les annonces a compter

Il faut choisir une regle metier claire:

- soit compter toutes les annonces de l'agence, y compris les brouillons;
- soit compter seulement les annonces publiees;
- soit compter les annonces publiees + brouillons avances.

Vu que le `Property` est cree des l'etape 1 du formulaire, la regle la plus stricte serait de compter tous les biens non supprimes.

## Politique pour les anciennes agences gratuites

Si l'admin coupe la gratuite, il faut choisir ce qui arrive aux agences deja en gratuit.

Option recommandee:

- les anciennes agences gardent leur gratuit jusqu'a la fin de leur periode ou jusqu'a leur limite actuelle;
- les nouvelles agences n'ont plus le plan gratuit;
- les anciennes ne peuvent pas creer de nouvelles annonces au-dela de leur quota;
- elles doivent passer sur un forfait payant pour continuer.

Option plus stricte:

- passer les anciens abonnements gratuits en `expired` ou `canceled`;
- bloquer toute nouvelle creation d'annonce tant qu'aucun forfait payant n'est actif.

## Conclusion

La BDD contient deja les bases necessaires pour piloter cette logique.

Ce qui manque pour rendre la regle robuste, c'est surtout:

1. creer automatiquement un `AgencySubscription` gratuit quand la gratuite est active;
2. bloquer la creation d'un `Property` si l'agence n'a pas de forfait valide ou si son quota est atteint;
3. utiliser les snapshots d'abonnement pour respecter l'historique;
4. ne jamais supprimer le plan gratuit, seulement le desactiver.

