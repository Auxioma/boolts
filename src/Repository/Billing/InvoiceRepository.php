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

namespace App\Repository\Billing;

use App\Entity\Billing\Invoice;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Invoice> */
final class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function findOneForAgency(int $id, User $agency): ?Invoice
    {
        return $this->findOneBy(['id' => $id, 'agency' => $agency]);
    }

    /**
     * @return list<Invoice>
     */
    public function findForAgency(User $agency, ?string $search = null): array
    {
        $qb = $this->createQueryBuilder('invoice')
            ->where('invoice.agency = :agency')
            ->setParameter('agency', $agency)
            ->orderBy('invoice.issuedAt', 'DESC')
            ->addOrderBy('invoice.createdAt', 'DESC');

        $search = null !== $search ? trim($search) : '';

        if ('' !== $search) {
            $qb->andWhere('invoice.number LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Factures de l'agence indexées par identifiant de paiement, pour associer
     * chaque ligne de l'historique des paiements à sa facture.
     *
     * @return array<int, Invoice>
     */
    public function findForAgencyIndexedByPaymentId(User $agency): array
    {
        $invoices = $this->createQueryBuilder('invoice')
            ->innerJoin('invoice.payment', 'payment')
            ->addSelect('payment')
            ->where('invoice.agency = :agency')
            ->setParameter('agency', $agency)
            ->orderBy('invoice.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();

        $indexed = [];

        foreach ($invoices as $invoice) {
            $paymentId = $invoice->getPayment()?->getId();

            if (null !== $paymentId) {
                $indexed[$paymentId] = $invoice;
            }
        }

        return $indexed;
    }
}
