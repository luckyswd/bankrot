<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\BankruptcyStage;
use App\Repository\CreditorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CreditorRepository::class)]
#[ORM\Table(name: 'creditors')]
class Creditor extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([BankruptcyStage::PRE_COURT->value, BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 500)]
    #[Groups([BankruptcyStage::PRE_COURT->value, BankruptcyStage::JUDICIAL_PROCEDURE->value])]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $headFullName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bankDetails = null;

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

    public function getHeadFullName(): ?string
    {
        return $this->headFullName;
    }

    public function setHeadFullName(?string $headFullName): self
    {
        $this->headFullName = $headFullName;

        return $this;
    }

    public function getBankDetails(): ?string
    {
        return $this->bankDetails;
    }

    public function setBankDetails(?string $bankDetails): self
    {
        $this->bankDetails = $bankDetails;

        return $this;
    }

    /**
     * Извлекает БИК из bankDetails.
     */
    public function getBik(): ?string
    {
        if ($this->bankDetails === null) {
            return null;
        }

        if (preg_match('/БИК:\s*(\d+)/ui', $this->bankDetails, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Извлекает ИНН из bankDetails.
     */
    public function getInn(): ?string
    {
        if ($this->bankDetails === null) {
            return null;
        }

        if (preg_match('/ИНН:\s*(\d+)/ui', $this->bankDetails, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Извлекает КПП из bankDetails.
     */
    public function getKpp(): ?string
    {
        if ($this->bankDetails === null) {
            return null;
        }

        if (preg_match('/КПП:\s*(\d+)/ui', $this->bankDetails, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Извлекает корреспондентский счет из bankDetails.
     */
    public function getCorrAccount(): ?string
    {
        if ($this->bankDetails === null) {
            return null;
        }

        if (preg_match('/КОРР\.?СЧЕТ:\s*(\d+)/ui', $this->bankDetails, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Извлекает расчетный счет из bankDetails.
     */
    public function getSettlementAccount(): ?string
    {
        if ($this->bankDetails === null) {
            return null;
        }

        if (preg_match('/РАСЧЕТНЫЙ\s+СЧЕТ:\s*(\d+)/ui', $this->bankDetails, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Извлекает ОГРН из bankDetails.
     */
    public function getOgrn(): ?string
    {
        if ($this->bankDetails === null) {
            return null;
        }

        if (preg_match('/ОГРН:\s*(\d+)/ui', $this->bankDetails, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Возвращает название кредитора в родительном падеже с маленькой буквы.
     * Преобразует "ПУБЛИЧНОЕ АКЦИОНЕРНОЕ ОБЩЕСТВО "СБЕРБАНК РОССИИ""
     * в "публичного акционерного общества «Сбербанк России»".
     */
    public function getNameGenitive(): string
    {
        $name = $this->name;

        // Извлекаем все названия в кавычках (может быть несколько уровней вложенности)
        $quotedNames = [];

        // Обрабатываем двойные кавычки "" (внутренние кавычки обрабатываем первыми)
        while (preg_match('/""([^"]+)""/', $name, $matches)) {
            $quotedNames[] = $matches[1];
            $name = preg_replace('/""[^"]+""/', '', $name, 1);
        }

        // Обрабатываем одинарные кавычки "
        while (preg_match('/"([^"]+)"/', $name, $matches)) {
            $quotedNames[] = $matches[1];
            $name = preg_replace('/"[^"]+"/', '', $name, 1);
        }

        // Обрабатываем кавычки-ёлочки «»
        while (preg_match('/«([^»]+)»/', $name, $matches)) {
            $quotedNames[] = $matches[1];
            $name = preg_replace('/«[^»]+»/', '', $name, 1);
        }

        // Убираем лишние пробелы
        $name = trim($name);
        $name = preg_replace('/\s+/', ' ', $name);

        // Преобразуем в нижний регистр
        $name = mb_strtolower($name, 'UTF-8');

        // Словарь склонений организационных форм (в порядке от длинных к коротким)
        $replacements = [
            'публичное акционерное общество' => 'публичного акционерного общества',
            'открытое акционерное общество' => 'открытого акционерного общества',
            'закрытое акционерное общество' => 'закрытого акционерного общества',
            'непубличное акционерное общество' => 'непубличного акционерного общества',
            'акционерное общество' => 'акционерного общества',
            'общество с ограниченной ответственностью' => 'общества с ограниченной ответственностью',
            'общество с дополнительной ответственностью' => 'общества с дополнительной ответственностью',
            'профессиональная коллекторская организация' => 'профессиональной коллекторской организации',
            'федеральная налоговая служба' => 'федеральной налоговой службы',
        ];

        // Применяем замены (от длинных к коротким)
        foreach ($replacements as $nominative => $genitive) {
            $pos = mb_stripos($name, $nominative);
            if ($pos !== false && $pos === 0) {
                $name = mb_substr($name, 0, $pos) . $genitive . mb_substr($name, $pos + mb_strlen($nominative));
                break;
            }
        }

        // Обрабатываем названия в кавычках
        if (!empty($quotedNames)) {
            foreach ($quotedNames as $quotedName) {
                $quotedName = trim($quotedName);
                $quotedNameLower = mb_strtolower($quotedName, 'UTF-8');

                // Проверяем, не является ли это организационной формой
                $isOrgForm = false;
                $processedQuotedName = $quotedNameLower;

                foreach ($replacements as $nominative => $genitive) {
                    if (mb_stripos($processedQuotedName, $nominative) === 0) {
                        // Это организационная форма, склоняем её
                        $processedQuotedName = str_ireplace($nominative, $genitive, $processedQuotedName);
                        $isOrgForm = true;
                        break;
                    }
                }

                if (!$isOrgForm) {
                    // Преобразуем в правильный регистр (каждое слово с заглавной)
                    $processedQuotedName = mb_convert_case($quotedName, MB_CASE_TITLE, 'UTF-8');
                }

                $name .= ' «' . $processedQuotedName . '»';
            }
        }

        return trim($name);
    }
}
