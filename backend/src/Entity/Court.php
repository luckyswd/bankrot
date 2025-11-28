<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CourtRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CourtRepository::class)]
#[ORM\Table(name: 'courts')]
class Court extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['pre_court'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 500)]
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

    /**
     * Возвращает название суда в творительном падеже с первой буквой в нижнем регистре.
     *
     * @return string Название суда в творительном падеже
     */
    public function getNameGenitive(): string
    {
        $name = $this->name;

        // Убираем "В " в начале, если есть
        if (mb_strpos($name, 'В ') === 0) {
            $name = mb_substr($name, 2);
        }

        // Заменяем "Арбитражный суд" на "арбитражным судом"
        $name = preg_replace(
            '/^Арбитражный суд\s+/u',
            'арбитражным судом ',
            $name
        );

        // Приводим первую букву к нижнему регистру
        if (mb_strlen($name) > 0) {
            $firstChar = mb_substr($name, 0, 1);
            $rest = mb_substr($name, 1);
            $name = mb_strtolower($firstChar) . $rest;
        }

        return trim($name);
    }

    public function getShortName(): string
    {
        $name = $this->name;

        return str_replace('Арбитражный суд ', '', $name);
    }
}
