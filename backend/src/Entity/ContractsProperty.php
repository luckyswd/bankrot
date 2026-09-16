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

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Вид и валюта счёта', type: 'string', example: 'текущий, рубли', nullable: true)]
    private ?string $accountType = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Дата открытия счёта', type: Types::STRING, format: 'date', example: '2019-03-12', nullable: true)]
    private ?\DateTimeInterface $openedAt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Сумма: остаток на счёте, наличные, уставный капитал, номинальная величина или сумма задолженности', type: 'string', example: '50000.00', nullable: true)]
    private ?string $amount = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Валюта', type: 'string', example: 'рубли', nullable: true)]
    private ?string $currency = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Лицо, выпустившее ценную бумагу', type: 'string', example: 'ПАО «Газпром»', nullable: true)]
    private ?string $issuer = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Доля участия', type: 'string', example: '25%', nullable: true)]
    private ?string $participationShare = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Общее количество', type: 'string', example: '100', nullable: true)]
    private ?string $quantity = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Содержание обязательства', type: 'string', example: 'заём по расписке', nullable: true)]
    private ?string $obligationContent = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Основание участия или возникновения', type: 'string', example: 'договор займа от 01.02.2020 г.', nullable: true)]
    private ?string $basisText = null;

    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Вид имущества', type: 'string', example: 'real_estate')]
    public function getKind(): PropertyKind
    {
        return $this->subtype->getKind();
    }

    public function getAccountType(): ?string
    {
        return $this->accountType;
    }

    public function setAccountType(?string $accountType): self
    {
        $this->accountType = $accountType;

        return $this;
    }

    public function getOpenedAt(): ?\DateTimeInterface
    {
        return $this->openedAt;
    }

    public function setOpenedAt(?\DateTimeInterface $openedAt): self
    {
        $this->openedAt = $openedAt;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    public function setIssuer(?string $issuer): self
    {
        $this->issuer = $issuer;

        return $this;
    }

    public function getParticipationShare(): ?string
    {
        return $this->participationShare;
    }

    public function setParticipationShare(?string $participationShare): self
    {
        $this->participationShare = $participationShare;

        return $this;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(?string $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getObligationContent(): ?string
    {
        return $this->obligationContent;
    }

    public function setObligationContent(?string $obligationContent): self
    {
        $this->obligationContent = $obligationContent;

        return $this;
    }

    public function getBasisText(): ?string
    {
        return $this->basisText;
    }

    public function setBasisText(?string $basisText): self
    {
        $this->basisText = $basisText;

        return $this;
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
