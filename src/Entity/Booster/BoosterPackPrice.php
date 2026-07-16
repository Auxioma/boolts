<?php

declare(strict_types=1);

namespace App\Entity\Booster;

use App\Entity\Shared\TimestampableTrait;
use App\Entity\Devise;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'booster_pack_price')]
#[ORM\UniqueConstraint(name: 'uniq_booster_pack_currency', columns: ['booster_pack_id', 'currency_id'])]
class BoosterPackPrice
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private BoosterPack $boosterPack;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Devise $currency;

    #[ORM\Column(type: 'bigint')]
    private int $amountMinor = 0;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $paymentProviderPriceId = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    public function __construct()
    {
        $this->initializeTimestamps();
    }

    public function getId(): ?int { return $this->id; }

    public function getBoosterPack(): BoosterPack { return $this->boosterPack; }
    public function setBoosterPack(BoosterPack $boosterPack): static { $this->boosterPack = $boosterPack; return $this; }

    public function getCurrency(): Devise { return $this->currency; }
    public function setCurrency(Devise $currency): static { $this->currency = $currency; return $this; }

    public function getAmountMinor(): int { return $this->amountMinor; }
    public function setAmountMinor(int $amountMinor): static { $this->amountMinor = $amountMinor; return $this; }

    public function getPaymentProviderPriceId(): ?string { return $this->paymentProviderPriceId; }
    public function setPaymentProviderPriceId(?string $paymentProviderPriceId): static { $this->paymentProviderPriceId = $paymentProviderPriceId; return $this; }

    public function isIsActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

}
