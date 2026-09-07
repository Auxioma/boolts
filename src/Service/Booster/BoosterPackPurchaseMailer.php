<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Service\Booster;

use App\Entity\Billing\Payment;
use App\Entity\Booster\BoosterPackPrice;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * E-mail transactionnel de confirmation envoyé à l'agence après l'achat
 * réussi d'un pack boost.
 *
 * Le contenu s'adapte au pack acheté : nombre de crédits Boost crédités,
 * durée de validité de chaque boost, montant réglé, référence de paiement
 * et date d'expiration des crédits.
 */
final readonly class BoosterPackPurchaseMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private UrlGeneratorInterface $urlGenerator,
        private string $mailerFromEmail,
        private string $mailerFromName,
    ) {
    }

    /**
     * Achat d'un pack boost confirmé : les crédits Boost viennent d'être
     * ajoutés au compte de l'agence.
     *
     * @param \DateTimeInterface|null $creditsExpireAt expiration des crédits achetés, si connue
     */
    public function sendPurchaseConfirmation(
        User $agency,
        BoosterPackPrice $boostPrice,
        Payment $payment,
        ?\DateTimeInterface $creditsExpireAt = null,
    ): void {
        $recipientEmail = mb_trim((string) $agency->getEmail());

        if ('' === $recipientEmail) {
            $this->logger->warning('Booster pack purchase confirmation skipped because agency email is empty.', [
                'agencyId' => $agency->getId(),
                'paymentReference' => $payment->getReference(),
            ]);

            return;
        }

        $pack = $boostPrice->getBoosterPack();
        $quantity = $pack->getBoostQuantity();

        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFromEmail, $this->mailerFromName))
            ->to(new Address($recipientEmail, $this->agencyName($agency)))
            ->subject(\sprintf('Confirmation de votre achat — Pack boost %s', $pack->getName()))
            ->htmlTemplate('email/booster/pack_purchase_confirmation.html.twig')
            ->context([
                'agencyName' => $this->agencyName($agency),
                'packName' => $pack->getName(),
                'packDescription' => $pack->getDescription(),
                'boostQuantity' => $quantity,
                'boostDurationDays' => $pack->getBoostDurationDays(),
                'amount' => $boostPrice->getAmountMinor() / 100,
                'currencySign' => $boostPrice->getCurrency()->getSigne() ?? '',
                'paymentReference' => $payment->getReference(),
                'purchasedAt' => $payment->getPaidAt() ?? new \DateTimeImmutable(),
                'creditsExpireAt' => $creditsExpireAt,
                'optionsUrl' => $this->urlGenerator->generate(
                    'agence_immobiliere_options',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
                'mesBiensUrl' => $this->urlGenerator->generate(
                    'agence_immobiliere_mes_biens_list',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
            ]);

        try {
            $this->mailer->send($email);
        } catch (\Throwable $exception) {
            $this->logger->error('Booster pack purchase confirmation could not be sent.', [
                'exception' => $exception,
                'agencyId' => $agency->getId(),
                'paymentReference' => $payment->getReference(),
            ]);
        }
    }

    /**
     * Nom d'affichage de l'agence : raison sociale, puis nom complet, puis
     * adresse e-mail en dernier recours.
     */
    private function agencyName(User $agency): string
    {
        $company = mb_trim((string) $agency->getEntreprise());

        if ('' !== $company) {
            return $company;
        }

        $fullName = mb_trim(\sprintf(
            '%s %s',
            (string) $agency->getPrenom(),
            (string) $agency->getNom(),
        ));

        if ('' !== $fullName) {
            return $fullName;
        }

        return mb_trim((string) $agency->getEmail());
    }
}
