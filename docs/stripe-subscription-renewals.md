# Renouvellements Stripe automatisés

## Architecture

La commande `app:subscriptions:process` orchestre uniquement le traitement. La logique métier est portée par les services de `src/Service/Subscription/` et les accès Stripe par `src/Service/Stripe/`.

Le système réutilise le modèle Billing existant :

- `App\Entity\Billing\AgencySubscription` représente l'abonnement.
- `App\Entity\Billing\Payment` représente un paiement finalisé ou échoué.
- `App\Entity\Billing\PaymentAttempt` représente chaque tentative.
- `App\Entity\Billing\SubscriptionHistory` historise les événements métier.
- `App\Entity\Billing\SubscriptionEmailLog` empêche les emails dupliqués.

Stripe reste la source de vérité. Le CRON ne crée pas une nouvelle charge arbitraire : il lit d'abord la Subscription Stripe, l'Invoice et le PaymentIntent. La stratégie par défaut est `app_driven`, c'est-à-dire que Boolts retente le paiement de l'invoice ouverte existante via `/v1/invoices/{id}/pay`. En production, Stripe Smart Retries doit donc être désactivé ou configuré pour ne pas relancer en parallèle. La stratégie `stripe_managed` est disponible pour basculer en surveillance pure.

## Workflow Renouvellement Réussi

1. `SubscriptionProcessor::processActiveSubscriptions()` récupère par lot les abonnements actifs proches de leur `currentPeriodEnd`.
2. `SubscriptionRenewalService` récupère la Subscription Stripe.
3. L'invoice la plus récente est inspectée.
4. Si elle est déjà payée, `SubscriptionPaymentService::recordPaidInvoice()` crée le paiement manquant si nécessaire.
5. La période locale, le prix Stripe, le produit Stripe, les compteurs d'échec et l'historique sont synchronisés dans une transaction Doctrine.

La contrainte unique sur `payment.provider_invoice_id` empêche un doublon si le CRON et le webhook `invoice.paid` traitent la même facture.

## Workflow Défaut De Paiement

1. `invoice.payment_failed` ou le CRON détecte une invoice ouverte non payée avec PaymentIntent échoué.
2. `SubscriptionPaymentRecoveryService::handleInvoicePaymentFailed()` passe l'abonnement en `PAST_DUE`, crée la tentative, incrémente le compteur et envoie l'email du premier échec une seule fois.
3. `nextPaymentRetryAt` programme la prochaine relance au lendemain.
4. `findFailedSubscriptionsToRetry()` ne sélectionne que les abonnements éligibles : statut récupérable, compteur inférieur à 5, deadline non dépassée et date de retry atteinte.
5. Avant chaque retry, l'invoice est relue chez Stripe. Si elle est payée, l'abonnement repasse `ACTIVE` sans nouveau prélèvement.
6. En mode `app_driven`, la relance utilise l'invoice existante avec une clé d'idempotence Stripe `subscription-invoice-pay-{invoiceId}-{attempt}`.
7. Après 5 échecs ou après 5 jours, une dernière vérification Stripe est obligatoire avant tout downgrade.

Une erreur réseau Stripe ou HTTP 5xx ne modifie pas `paymentFailureCount`.

## Workflow Résiliation

1. `POST /account/subscription/cancel` appelle `SubscriptionCancellationService::requestCancellation()`.
2. Stripe est mis à jour avec `cancel_at_period_end=true`.
3. La base passe en `CANCEL_SCHEDULED`, sans downgrade immédiat.
4. Le client conserve les droits payants jusqu'à `currentPeriodEnd`.
5. `processCanceledSubscriptions()` vérifie Stripe après la fin de période.
6. Si Stripe confirme la fin de l'abonnement, `SubscriptionDowngradeService` crée ou ouvre un forfait FREE et `SubscriptionEntitlementService` applique les limites gratuites.

`POST /account/subscription/reactivate` annule la résiliation tant que la période n'est pas terminée.

## Modèle De Données

La migration `migrations/Version20260826154500.php` ajoute :

- champs Stripe et recouvrement sur `agency_subscription`;
- périodes facturées et numéro de tentative sur `payment`;
- lien direct `PaymentAttempt -> AgencySubscription`;
- table `subscription_history`;
- table `subscription_email_log`;
- indexes de batch et contraintes uniques idempotentes.

Pour PostgreSQL, une contrainte unique partielle protège contre plusieurs abonnements payants ouverts pour la même agence.

## Fichiers Principaux

- `src/Command/ProcessSubscriptionsCommand.php`
- `src/Controller/Account/SubscriptionLifecycleController.php`
- `src/Controller/StripeWebhookController.php`
- `src/Entity/Billing/AgencySubscription.php`
- `src/Entity/Billing/Payment.php`
- `src/Entity/Billing/PaymentAttempt.php`
- `src/Entity/Billing/SubscriptionHistory.php`
- `src/Entity/Billing/SubscriptionEmailLog.php`
- `src/Entity/Billing/Enum/SubscriptionStatus.php`
- `src/Entity/Billing/Enum/SubscriptionBillingPeriod.php`
- `src/Entity/Billing/Enum/SubscriptionHistoryEventType.php`
- `src/Entity/Billing/Enum/SubscriptionEmailType.php`
- `src/Entity/Billing/Enum/DowngradeReason.php`
- `src/Repository/Billing/AgencySubscriptionRepository.php`
- `src/Repository/Billing/PaymentRepository.php`
- `src/Repository/Billing/PaymentAttemptRepository.php`
- `src/Service/Stripe/StripeCustomerService.php`
- `src/Service/Stripe/StripeSubscriptionService.php`
- `src/Service/Stripe/StripeInvoiceService.php`
- `src/Service/Stripe/StripePaymentService.php`
- `src/Service/Subscription/SubscriptionProcessor.php`
- `src/Service/Subscription/SubscriptionRenewalService.php`
- `src/Service/Subscription/SubscriptionPaymentService.php`
- `src/Service/Subscription/SubscriptionPaymentRecoveryService.php`
- `src/Service/Subscription/SubscriptionCancellationService.php`
- `src/Service/Subscription/SubscriptionDowngradeService.php`
- `src/Service/Subscription/SubscriptionEntitlementService.php`
- `src/Service/Subscription/SubscriptionSynchronizationService.php`
- `src/Message/Billing/SendSubscriptionEmailMessage.php`
- `src/MessageHandler/Billing/SendSubscriptionEmailMessageHandler.php`
- `templates/emails/subscription/*.html.twig`

## Configuration

Variables d'environnement :

```dotenv
STRIPE_SECRET_KEY=sk_live_xxx
STRIPE_PUBLIC_KEY=pk_live_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
APP_URL=https://boolts.com
LOCK_DSN=flock
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
```

Paramètres applicatifs :

```yaml
app.subscription_batch_size: 100
app.subscription_retry_strategy: 'app_driven'
```

CRON recommandé :

```cron
*/15 * * * * php /chemin/projet/bin/console app:subscriptions:process --env=prod
```

Messenger doit être consommé en production :

```bash
php bin/console messenger:consume async --env=prod --time-limit=3600
```

## Webhooks Stripe

Endpoint :

```text
POST /stripe/webhook
```

Événements gérés :

- `invoice.paid`
- `invoice.payment_failed`
- `customer.subscription.updated`
- `customer.subscription.deleted`

Chaque `event.id` est persisté dans `payment_webhook_event`; les doublons sont ignorés.

## Logs

Le canal Monolog `subscription` écrit dans :

```text
var/log/{env}.subscription.log
```

Les logs contiennent les identifiants techniques nécessaires au rapprochement sans donnée PCI sensible.

## Scénarios Stripe Test Mode

À valider en environnement de test Stripe :

- renouvellement avec carte acceptée ;
- facture déjà payée puis CRON rejoué deux fois ;
- carte refusée ;
- fonds insuffisants ;
- carte expirée ;
- authentification 3D Secure ;
- carte mise à jour depuis le Customer Portal ;
- paiement régularisé après troisième ou quatrième tentative ;
- cinquième tentative échouée ;
- deadline de cinq jours dépassée ;
- résiliation à mi-période ;
- fin de période après `cancel_at_period_end`;
- `invoice.paid` reçu pendant que le CRON traite la même invoice.
