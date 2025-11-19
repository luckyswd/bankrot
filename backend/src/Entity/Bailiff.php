<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BailiffRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BailiffRepository::class)]
#[ORM\Table(name: 'bailiffs')]
class Bailiff extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 500)]
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
}
