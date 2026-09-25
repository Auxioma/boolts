# Plan de maintenance CSS

## Objectif

Réorganiser et fiabiliser le CSS du site afin de le rendre plus lisible, cohérent et maintenable, sans modifier :

- les classes HTML existantes ;
- les IDs existants ;
- la structure HTML ;
- les sélecteurs utilisés par JavaScript ;
- le comportement fonctionnel du site.

## Corrections à prévoir

### 1. Centraliser les variables CSS

Regrouper dans `assets/styles/base/variables.css` toutes les variables globales utilisées par le projet :

- couleurs principales et secondaires ;
- couleurs de texte, de fond, de bordure et d'erreur ;
- familles de polices ;
- tailles et poids de texte ;
- hauteurs de ligne ;
- rayons de bordure ;
- ombres ;
- transitions ;
- espacements récurrents ;
- dimensions globales, notamment la hauteur de la topbar.

Les variables actuellement déclarées dans des fichiers de pages, notamment `assets/styles/public/home.css`, devront être déplacées vers ce fichier central.

### 2. Compléter les variables manquantes

Vérifier et définir correctement les variables utilisées dans les différents fichiers CSS, notamment :

- `--color-primary`
- `--color-primary-hover`
- `--color-text`
- `--color-black`
- `--color-white`
- `--color-gray`
- `--color-gray-light`
- `--color-link-hover`
- `--font-family-base`
- `--topbar-height`
- `--transition-fast`
- `--radius-sm`
- `--radius-md`
- `--radius-pill`

Les noms devront être uniformisés afin d'éviter plusieurs variables pour une même valeur.

### 3. Remplacer les valeurs répétées en dur

Remplacer progressivement les valeurs répétées par des variables CSS :

- couleurs hexadécimales ;
- couleurs `rgba()` ;
- rayons de bordure ;
- ombres ;
- transitions ;
- espacements ;
- tailles communes ;
- hauteurs de composants.

Les valeurs spécifiques à un seul composant pourront rester locales lorsqu'elles ne correspondent pas à un token global.

### 4. Réorganiser l'ordre des imports

Conserver une organisation claire dans `assets/styles/app.css` :

1. variables ;
2. polices ;
3. styles globaux et reset ;
4. utilitaires ;
5. composants communs ;
6. layouts ;
7. pages publiques ;
8. dashboard ;
9. responsive ;
10. styles de plugins.

L'ordre devra respecter les dépendances CSS et éviter les surcharges involontaires.

### 5. Supprimer les doublons et conflits

Rechercher et corriger :

- les propriétés déclarées plusieurs fois dans un même bloc ;
- les règles identiques répétées ;
- les sélecteurs qui se contredisent ;
- les propriétés redéfinies plus loin dans un fichier ;
- les règles responsive dispersées ou difficiles à suivre.

Les sélecteurs existants devront être conservés.

### 6. Uniformiser le formatage

Appliquer une présentation homogène à tous les fichiers CSS concernés :

- une propriété par ligne ;
- indentation régulière ;
- blocs espacés de manière cohérente ;
- media queries lisibles ;
- regroupement logique des propriétés ;
- regroupement des sélecteurs partageant les mêmes règles.

### 7. Harmoniser les composants communs

Uniformiser les styles des éléments similaires :

- boutons primaires ;
- boutons secondaires ;
- boutons avec bordure ;
- boutons désactivés ;
- champs de formulaire ;
- liens ;
- états `hover`, `focus` et `active` ;
- cartes ;
- séparateurs ;
- éléments de navigation.

Cette harmonisation devra utiliser les variables globales sans renommer les classes existantes.

### 8. Corriger les erreurs CSS invalides

Corriger les déclarations invalides ou suspectes identifiées, notamment :

- `width: 30 x;` dans `assets/styles/layouts/topbar.css` ;
- les variables appelées mais non définies ;
- les valeurs incohérentes pour une même propriété ;
- les déclarations devenues inutiles après nettoyage.

### 9. Vérifier les fichiers responsive

Revoir les media queries afin de :

- regrouper les règles par composant ;
- éviter les surcharges inutiles ;
- conserver les comportements desktop, tablette et mobile ;
- vérifier les seuils de largeur ;
- préserver les priorités CSS actuelles.

### 10. Vérifier les fichiers de plugins et d'e-mails

Séparer clairement les styles propres au site des styles externes ou autonomes :

- `assets/styles/plugin/intl-tel-input/` ;
- `assets/fonts/lucide.css` ;
- `templates/email/`.

Ces fichiers ne devront être modifiés que si une correction est nécessaire et directement liée à l'organisation CSS.

## Contraintes à respecter

- Ne modifier aucune classe.
- Ne modifier aucun ID.
- Ne pas modifier les templates HTML.
- Ne pas modifier le JavaScript.
- Ne pas changer les noms utilisés par les scripts.
- Ne pas changer le rendu volontairement sans validation.
- Ne pas supprimer une règle sans vérifier son utilisation.
- Ne pas mélanger les styles de pages différentes sans conserver leur portée.

## Validation prévue

Après les corrections :

1. vérifier que toutes les variables utilisées sont définies ;
2. vérifier qu'il ne reste aucune déclaration CSS invalide ;
3. vérifier les imports et l'ordre de chargement ;
4. tester les pages publiques ;
5. tester les pages du dashboard ;
6. tester les formulaires ;
7. vérifier les affichages mobile et desktop ;
8. contrôler que les classes et IDs restent inchangés ;
9. comparer visuellement les principaux composants avant et après.

## Statut

- [ ] Centraliser les variables CSS
- [ ] Compléter les variables manquantes
- [ ] Remplacer les valeurs répétées
- [ ] Réorganiser les imports
- [ ] Supprimer les doublons et conflits
- [ ] Uniformiser le formatage
- [ ] Harmoniser les composants
- [ ] Corriger les erreurs CSS invalides
- [ ] Revoir le responsive
- [ ] Effectuer la validation finale
