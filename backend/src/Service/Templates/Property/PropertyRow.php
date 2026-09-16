<?php

declare(strict_types=1);

namespace App\Service\Templates\Property;

final readonly class PropertyRow
{
    public function __construct(
        private string $name,
        private string $ownershipType,
        private string $location,
        private string $area,
        private string $identificationNumber,
        private string $pledgeInfo,
        private string $managerValuation,
        private string $appraiserValuation,
        private string $exclusionText,
        private string $accountType,
        private string $openedAt,
        private string $amount,
        private string $currency,
        private string $issuer,
        private string $participationShare,
        private string $quantity,
        private string $obligationContent,
        private string $basisText,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOwnershipType(): string
    {
        return $this->ownershipType;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getArea(): string
    {
        return $this->area;
    }

    public function getIdentificationNumber(): string
    {
        return $this->identificationNumber;
    }

    public function getPledgeInfo(): string
    {
        return $this->pledgeInfo;
    }

    public function getManagerValuation(): string
    {
        return $this->managerValuation;
    }

    public function getAppraiserValuation(): string
    {
        return $this->appraiserValuation;
    }

    public function getExclusionText(): string
    {
        return $this->exclusionText;
    }

    public function getAccountType(): string
    {
        return $this->accountType;
    }

    public function getOpenedAt(): string
    {
        return $this->openedAt;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getIssuer(): string
    {
        return $this->issuer;
    }

    public function getParticipationShare(): string
    {
        return $this->participationShare;
    }

    public function getQuantity(): string
    {
        return $this->quantity;
    }

    public function getObligationContent(): string
    {
        return $this->obligationContent;
    }

    public function getBasisText(): string
    {
        return $this->basisText;
    }
}
