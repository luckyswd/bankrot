<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\BankruptcyStage;
use App\Repository\RosgvardiaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: RosgvardiaRepository::class)]
#[ORM\Table(name: 'rosgvardia')]
class Rosgvardia extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 500)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }
}
