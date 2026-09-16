<?php

declare(strict_types=1);

namespace App\Service\Templates\CreditorsRegister;

final readonly class RegistryClaimRow
{
    public function __construct(
        private string $entryDate,
        private int $creditorNumber,
        private int $claimNumber,
        private string $creditorName,
        private string $creditorAddress,
        private string $creditorHeadFullName,
        private string $creditorBankDetails,
        private string $kind,
        private string $basisText,
        private string $originDate,
        private string $amountText,
        private string $determinationText,
    ) {
    }

    public function getEntryDate(): string
    {
        return $this->entryDate;
    }

    public function getCreditorNumber(): int
    {
        return $this->creditorNumber;
    }

    public function getClaimNumber(): int
    {
        return $this->claimNumber;
    }

    public function getCreditorName(): string
    {
        return $this->creditorName;
    }

    public function getCreditorAddress(): string
    {
        return $this->creditorAddress;
    }

    public function getCreditorHeadFullName(): string
    {
        return $this->creditorHeadFullName;
    }

    public function getCreditorBankDetails(): string
    {
        return $this->creditorBankDetails;
    }

    public function getKind(): string
    {
        return $this->kind;
    }

    public function getBasisText(): string
    {
        return $this->basisText;
    }

    public function getOriginDate(): string
    {
        return $this->originDate;
    }

    public function getAmountText(): string
    {
        return $this->amountText;
    }

    public function getDeterminationText(): string
    {
        return $this->determinationText;
    }
}
