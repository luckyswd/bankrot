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
}
