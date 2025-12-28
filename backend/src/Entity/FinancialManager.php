<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FinancialManagerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: FinancialManagerRepository::class)]
#[ORM\Table(name: 'financial_managers')]
class FinancialManager extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['basic_info'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups(['basic_info'])]
    private ?string $fio = null;

    #[ORM\Column(type: Types::STRING, length: 12, nullable: true)]
    private ?string $inn = null;

    #[ORM\Column(type: Types::STRING, length: 14, nullable: true)]
    private ?string $snils = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $arbitrationManagerRegistryNumber = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $aauName = null;

    #[ORM\Column(type: Types::STRING, length: 15, nullable: true)]
    private ?string $aauOgrn = null;

    #[ORM\Column(type: Types::STRING, length: 12, nullable: true)]
    private ?string $aauInn = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $aauAddress = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFio(): ?string
    {
        return $this->fio;
    }

    public function setFio(?string $fio): self
    {
        $this->fio = $fio;

        return $this;
    }

    public function getInn(): ?string
    {
        return $this->inn;
    }

    public function setInn(?string $inn): self
    {
        $this->inn = $inn;

        return $this;
    }

    public function getSnils(): ?string
    {
        return $this->snils;
    }

    public function setSnils(?string $snils): self
    {
        $this->snils = $snils;

        return $this;
    }

    public function getArbitrationManagerRegistryNumber(): ?string
    {
        return $this->arbitrationManagerRegistryNumber;
    }

    public function setArbitrationManagerRegistryNumber(?string $arbitrationManagerRegistryNumber): self
    {
        $this->arbitrationManagerRegistryNumber = $arbitrationManagerRegistryNumber;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAauName(): ?string
    {
        return $this->aauName;
    }

    public function setAauName(?string $aauName): self
    {
        $this->aauName = $aauName;

        return $this;
    }

    public function getAauOgrn(): ?string
    {
        return $this->aauOgrn;
    }

    public function setAauOgrn(?string $aauOgrn): self
    {
        $this->aauOgrn = $aauOgrn;

        return $this;
    }

    public function getAauInn(): ?string
    {
        return $this->aauInn;
    }

    public function setAauInn(?string $aauInn): self
    {
        $this->aauInn = $aauInn;

        return $this;
    }

    public function getAauAddress(): ?string
    {
        return $this->aauAddress;
    }

    public function setAauAddress(?string $aauAddress): self
    {
        $this->aauAddress = $aauAddress;

        return $this;
    }

    /**
     * Получить сокращенное ФИО в формате "И.О. Фамилия".
     *
     * Пример: "Федорец Василий Владимирович" -> "В.В. Федорец"
     *
     * @return string|null Сокращенное ФИО или null, если ФИО не указано
     */
    public function getShortFio(): ?string
    {
        if (empty($this->fio)) {
            return null;
        }

        $parts = array_filter(array_map('trim', explode(' ', $this->fio)));

        if (empty($parts)) {
            return null;
        }

        // Если только одна часть - возвращаем как есть
        if (count($parts) === 1) {
            return $parts[0];
        }

        // Если две части - считаем что это "Фамилия Имя", возвращаем "И. Фамилия"
        if (count($parts) === 2) {
            $lastName = $parts[0];
            $firstName = mb_substr($parts[1], 0, 1, 'UTF-8') . '.';

            return $firstName . ' ' . $lastName;
        }

        // Если три и более частей - формат "Фамилия Имя Отчество", возвращаем "И.О. Фамилия"
        $lastName = $parts[0];
        $firstNameInitial = mb_substr($parts[1], 0, 1, 'UTF-8') . '.';
        $middleNameInitial = mb_substr($parts[2], 0, 1, 'UTF-8') . '.';

        return $firstNameInitial . $middleNameInitial . ' ' . $lastName;
    }
}
