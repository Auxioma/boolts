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

namespace App\Security\Voter;

use App\Entity\Enum\DocumentSubmissionStatus;
use App\Entity\User;
use App\Repository\Document\UserDocumentSubmissionRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, mixed>
 */
final class AgencyDocumentVoter extends Voter
{
    public const ACCESS_RESTRICTED_DASHBOARD = 'AGENCY_DOCUMENTS_ACCESS_RESTRICTED_DASHBOARD';

    private const BLOCKING_STATUSES = [
        DocumentSubmissionStatus::PENDING,
        DocumentSubmissionStatus::REJECTED,
    ];

    private const APPROVED_STATUSES = [
        DocumentSubmissionStatus::APPROVED,
    ];

    public function __construct(
        private readonly UserDocumentSubmissionRepository $submissionRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::ACCESS_RESTRICTED_DASHBOARD === $attribute;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null,
    ): bool {
        $user = $token->getUser();

        if (!$user instanceof User || !\in_array('ROLE_AGENCE', $user->getRoles(), true)) {
            return false;
        }

        return $this->submissionRepository->hasLatestSubmissionWithStatus($user, self::APPROVED_STATUSES)
            && !$this->submissionRepository->hasLatestSubmissionWithStatus($user, self::BLOCKING_STATUSES);
    }
}
