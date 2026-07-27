<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AgencyProfileDailyVisitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgencyProfileDailyVisitRepository::class)]
#[ORM\Table(name: 'agency_profile_daily_visit')]
#[ORM\UniqueConstraint(name: 'uniq_agency_profile_daily_visit', columns: ['agency_id', 'viewed_on'])]
class AgencyProfileDailyVisit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $agency = null;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeImmutable $viewedOn = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $visits = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgency(): ?User
    {
        return $this->agency;
    }

    public function setAgency(?User $agency): static
    {
        $this->agency = $agency;

        return $this;
    }

    public function getViewedOn(): ?\DateTimeImmutable
    {
        return $this->viewedOn;
    }

    public function setViewedOn(\DateTimeImmutable $viewedOn): static
    {
        $this->viewedOn = $viewedOn->setTime(0, 0);

        return $this;
    }

    public function getVisits(): int
    {
        return $this->visits;
    }

    public function setVisits(int $visits): static
    {
        $this->visits = max(0, $visits);

        return $this;
    }

    public function incrementVisits(): static
    {
        ++$this->visits;

        return $this;
    }
}
