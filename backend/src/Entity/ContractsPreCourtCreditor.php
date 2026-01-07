<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\BankruptcyStage;
use App\Repository\ContractsPreCourtCreditorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ContractsPreCourtCreditorRepository::class)]
#[ORM\Table(name: 'contracts_pre_court_creditors')]
#[ORM\UniqueConstraint(name: 'contract_creditor_pre_court_unique', columns: ['contract_id', 'creditor_id'])]
class ContractsPreCourtCreditor extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: 'ID связи', type: 'integer', example: 1)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Contracts::class, inversedBy: 'preCourtCreditors')]
    #[ORM\JoinColumn(name: 'contract_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Contracts $contract;

    #[ORM\ManyToOne(targetEntity: Creditor::class)]
    #[ORM\JoinColumn(name: 'creditor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: 'Кредитор', type: 'object')]
    private Creditor $creditor;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: '№ Кредитного договора', type: Types::STRING, example: '118270753', nullable: true)]
    private ?string $creditContractNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: 'Дата Кредитного договора', type: 'string', format: 'date', example: '2025-01-15', nullable: true)]
    private ?\DateTimeInterface $creditContractDate = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: 'Сумма долга', type: Types::STRING, example: '1000000.00', nullable: true)]
    private ?string $debtAmount = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: 'Основной долг', type: Types::STRING, example: '800000.00', nullable: true)]
    private ?string $principalAmount = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: 'Финансовые санкции', type: Types::STRING, example: '200000.00', nullable: true)]
    private ?string $financialSanctions = null;

    /**
     * Виртуальное поле для сериализации ID кредитора.
     */
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: 'ID кредитора', type: 'integer', example: 1)]
    public function getCreditorId(): ?int
    {
        return $this->creditor->getId();
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

    public function getCreditor(): Creditor
    {
        return $this->creditor;
    }

    public function setCreditor(Creditor $creditor): self
    {
        $this->creditor = $creditor;

        return $this;
    }

    public function getCreditContractNumber(): ?string
    {
        return $this->creditContractNumber;
    }

    public function setCreditContractNumber(?string $creditContractNumber): self
    {
        $this->creditContractNumber = $creditContractNumber;

        return $this;
    }

    public function getCreditContractDate(): ?\DateTimeInterface
    {
        return $this->creditContractDate;
    }

    public function setCreditContractDate(?\DateTimeInterface $creditContractDate): self
    {
        $this->creditContractDate = $creditContractDate;

        return $this;
    }

    public function getDebtAmount(): ?string
    {
        return $this->debtAmount;
    }

    public function setDebtAmount(?string $debtAmount): self
    {
        $this->debtAmount = $debtAmount;

        return $this;
    }

    public function getPrincipalAmount(): ?string
    {
        return $this->principalAmount;
    }

    public function setPrincipalAmount(?string $principalAmount): self
    {
        $this->principalAmount = $principalAmount;

        return $this;
    }

    public function getFinancialSanctions(): ?string
    {
        return $this->financialSanctions;
    }

    public function setFinancialSanctions(?string $financialSanctions): self
    {
        $this->financialSanctions = $financialSanctions;

        return $this;
    }

    public function basisOccurrence(): string
    {
        if (empty($this->creditContractDate)) {
            return '';
        }

        $formattedDate = $this->creditContractDate->format('d.m.Y');
        $contractNumber = $this->creditContractNumber;

        if (!empty($contractNumber)) {
            return sprintf('Кредитный договор №%s от %sг.', $contractNumber, $formattedDate);
        }

        return sprintf('Кредитный договор от %sг.', $formattedDate);
    }
}
