<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class BaseEntity
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected \DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected \DateTime $updatedAt;

    public function __construct()
    {
        $now = new \DateTime();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function touchUpdatedAt(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function setCreatedAt(\DateTime $createdAt): static { $this->createdAt = $createdAt; return $this; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function setUpdatedAt(\DateTime $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }
}
