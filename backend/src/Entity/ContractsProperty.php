<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\BankruptcyStage;
use App\Entity\Enum\PropertyKind;
use App\Entity\Enum\PropertySubtype;
use App\Repository\ContractsPropertyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ContractsPropertyRepository::class)]
#[ORM\Table(name: 'contracts_property')]
class ContractsProperty extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'ID записи об имуществе', type: 'integer', example: 1)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Contracts::class, inversedBy: 'property')]
    #[ORM\JoinColumn(name: 'contract_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Contracts $contract;

    #[ORM\Column(type: Types::STRING, length: 50, enumType: PropertySubtype::class)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Подвид имущества', type: 'string', example: 'apartment')]
    private PropertySubtype $subtype = PropertySubtype::OTHER_REAL_ESTATE;

    #[ORM\Column(type: Types::STRING, length: 500)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Вид и наименование имущества', type: 'string', example: 'квартира в многоквартирном доме, кадастровый номер 78:15:0000:15:350')]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Вид собственности', type: 'string', example: 'общая долевая собственность, доля в праве ½', nullable: true)]
    private ?string $ownershipType = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Местонахождение имущества', type: 'string', example: 'г. Санкт-Петербург, ул. Савушкина, д. 18, кв. 18', nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Площадь в квадратных метрах', type: 'string', example: '78,8', nullable: true)]
    private ?string $area = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Идентификационный номер транспортного средства', type: 'string', example: 'XTA210740A2565742', nullable: true)]
    private ?string $identificationNumber = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Сведения о залоге и залогодержателе', type: 'string', example: 'залог ПАО «Сбербанк России»', nullable: true)]
    private ?string $pledgeInfo = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Стоимость, определённая финансовым управляющим', type: 'string', example: '1000000.00', nullable: true)]
    private ?string $managerValuation = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Стоимость, определённая оценщиком', type: 'string', example: '1200000.00', nullable: true)]
    private ?string $appraiserValuation = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Исключается из конкурсной массы', type: 'boolean', example: false, nullable: true)]
    private ?bool $isExcludedFromEstate = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Основание исключения из конкурсной массы', type: 'string', example: 'единственное пригодное для проживания жильё', nullable: true)]
    private ?string $exclusionReason = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Стоимость имущества, исключаемого из конкурсной массы', type: 'string', example: '1000000.00', nullable: true)]
    private ?string $excludedValuation = null;

    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Вид имущества', type: 'string', example: 'real_estate')]
    public function getKind(): PropertyKind
    {
        return $this->subtype->getKind();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContract(): Contracts
    {
        return $this->contract;
    }

    public function setContract(Contracts $contract): self
    {
        $this->contract = $contract;

        return $this;
    }

    public function getSubtype(): PropertySubtype
    {
        return $this->subtype;
    }

    public function setSubtype(PropertySubtype $subtype): self
    {
        $this->subtype = $subtype;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOwnershipType(): ?string
    {
        return $this->ownershipType;
    }

    public function setOwnershipType(?string $ownershipType): self
    {
        $this->ownershipType = $ownershipType;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getArea(): ?string
    {
        return $this->area;
    }

    public function setArea(?string $area): self
    {
        $this->area = $area;

        return $this;
    }

    public function getIdentificationNumber(): ?string
    {
        return $this->identificationNumber;
    }

    public function setIdentificationNumber(?string $identificationNumber): self
    {
        $this->identificationNumber = $identificationNumber;

        return $this;
    }

    public function getPledgeInfo(): ?string
    {
        return $this->pledgeInfo;
    }

    public function setPledgeInfo(?string $pledgeInfo): self
    {
        $this->pledgeInfo = $pledgeInfo;

        return $this;
    }

    public function getManagerValuation(): ?string
    {
        return $this->managerValuation;
    }

    public function setManagerValuation(?string $managerValuation): self
    {
        $this->managerValuation = $managerValuation;

        return $this;
    }

    public function getAppraiserValuation(): ?string
    {
        return $this->appraiserValuation;
    }

    public function setAppraiserValuation(?string $appraiserValuation): self
    {
        $this->appraiserValuation = $appraiserValuation;

        return $this;
    }

    public function getIsExcludedFromEstate(): ?bool
    {
        return $this->isExcludedFromEstate;
    }

    public function setIsExcludedFromEstate(?bool $isExcludedFromEstate): self
    {
        $this->isExcludedFromEstate = $isExcludedFromEstate;

        return $this;
    }

    public function getExclusionReason(): ?string
    {
        return $this->exclusionReason;
    }

    public function setExclusionReason(?string $exclusionReason): self
    {
        $this->exclusionReason = $exclusionReason;

        return $this;
    }

    public function getExcludedValuation(): ?string
    {
        return $this->excludedValuation;
    }

    public function setExcludedValuation(?string $excludedValuation): self
    {
        $this->excludedValuation = $excludedValuation;

        return $this;
    }
}
