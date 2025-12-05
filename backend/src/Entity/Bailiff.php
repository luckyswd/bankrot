<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\BankruptcyStage;
use App\Repository\BailiffRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: BailiffRepository::class)]
#[ORM\Table(name: 'bailiffs')]
class Bailiff extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 500)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    private string $department;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $headFullName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepartment(): string
    {
        return $this->department;
    }

    public function setDepartment(string $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getHeadFullName(): ?string
    {
        return $this->headFullName;
    }

    public function setHeadFullName(?string $headFullName): self
    {
        $this->headFullName = $headFullName;

        return $this;
    }

    public function getShortHeadFullName(): ?string
    {
        if (empty($this->headFullName)) {
            return null;
        }

        $parts = preg_split('/\s+/', trim($this->headFullName));

        if (!$parts) {
            return null;
        }

        $count = count($parts);

        if ($count === 1) {
            return $parts[0];
        }

        $surname = $parts[0];
        $name = $parts[1];

        if ($count === 2) {

            return sprintf('%s %s.', $surname, mb_substr($name, 0, 1));
        }

        $middle = $parts[2];

        $result = sprintf(
            '%s %s.%s.',
            $surname,
            mb_substr($name, 0, 1),
            mb_substr($middle, 0, 1)
        );

        return mb_strtoupper($result);
    }

    public function getFullHeadName(): ?string
    {
        return 'Судебному приставу-исполнителю – ' . $this->getShortHeadFullName();
    }
}
