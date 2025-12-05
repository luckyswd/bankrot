<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\BankruptcyStage;
use App\Repository\ContractsCreditorsClaimRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ContractsCreditorsClaimRepository::class)]
#[ORM\Table(name: 'contracts_creditors_claim')]
#[ORM\UniqueConstraint(name: 'contract_creditor_claim_unique', columns: ['contract_id', 'creditor_id'])]
class ContractsCreditorsClaim extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'ID связи', type: 'integer', example: 1)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Contracts::class, inversedBy: 'creditorsClaims')]
    #[ORM\JoinColumn(name: 'contract_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Contracts $contract;

    #[ORM\ManyToOne(targetEntity: Creditor::class)]
    #[ORM\JoinColumn(name: 'creditor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Кредитор', type: 'object')]
    private Creditor $creditor;

    /**
     * Виртуальное поле для сериализации ID кредитора.
     */
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'ID кредитора', type: 'integer', example: 1)]
    public function getCreditorId(): ?int
    {
        return $this->creditor->getId();
    }

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Сумма долга', type: Types::STRING, example: '1000000.00', nullable: true)]
    private ?string $debtAmount = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Основная сумма', type: Types::STRING, example: '800000.00', nullable: true)]
    private ?string $principalAmount = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Процент', type: Types::STRING, example: '100000.00', nullable: true)]
    private ?string $interest = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Штраф', type: Types::STRING, example: '50000.00', nullable: true)]
    private ?string $penalty = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Пеня', type: Types::STRING, example: '30000.00', nullable: true)]
    private ?string $lateFee = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Неустойка', type: Types::STRING, example: '20000.00', nullable: true)]
    private ?string $forfeiture = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Госпошлина', type: Types::STRING, example: '3000.00', nullable: true)]
    private ?string $stateDuty = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(
        description: 'Основания (массив с номером и датой)',
        type: 'array',
        items: new OA\Items(
            properties: [
                new OA\Property(property: 'number', type: Types::STRING, example: '123'),
                new OA\Property(property: 'date', type: Types::STRING, format: 'date', example: '2025-01-15'),
            ],
            type: 'object'
        ),
        nullable: true
    )]
    private ?array $basis = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    #[OA\Property(description: 'Требования получено', type: 'boolean', example: true, nullable: true)]
    private ?bool $inclusion = null;

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

    public function getInterest(): ?string
    {
        return $this->interest;
    }

    public function setInterest(?string $interest): self
    {
        $this->interest = $interest;

        return $this;
    }

    public function getPenalty(): ?string
    {
        return $this->penalty;
    }

    public function setPenalty(?string $penalty): self
    {
        $this->penalty = $penalty;

        return $this;
    }

    public function getLateFee(): ?string
    {
        return $this->lateFee;
    }

    public function setLateFee(?string $lateFee): self
    {
        $this->lateFee = $lateFee;

        return $this;
    }

    public function getForfeiture(): ?string
    {
        return $this->forfeiture;
    }

    public function setForfeiture(?string $forfeiture): self
    {
        $this->forfeiture = $forfeiture;

        return $this;
    }

    public function getStateDuty(): ?string
    {
        return $this->stateDuty;
    }

    public function setStateDuty(?string $stateDuty): self
    {
        $this->stateDuty = $stateDuty;

        return $this;
    }

    /**
     * @return array<int, array{number: string, date: string}>|null
     */
    public function getBasis(): ?array
    {
        return $this->basis;
    }

    /**
     * @param array<int, array{number: string, date: string}>|null $basis
     */
    public function setBasis(?array $basis): self
    {
        $this->basis = $basis;

        return $this;
    }

    public function getInclusion(): ?bool
    {
        return $this->inclusion;
    }

    public function setInclusion(?bool $inclusion): self
    {
        $this->inclusion = $inclusion;

        return $this;
    }
}
