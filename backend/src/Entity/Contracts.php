<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\ContractStatus;
use App\Repository\ContractsRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ContractsRepository::class)]
#[OA\Schema(
    schema: 'Contracts',
    type: 'object'
)]
class Contracts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'ID контракта', type: 'integer', example: 1)]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Имя должника', type: 'string', example: 'Иван', nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Фамилия должника', type: 'string', example: 'Иванов', nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Отчество должника', type: 'string', example: 'Иванович', nullable: true)]
    private ?string $middleName = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Изменялось ли ФИО должника', type: 'boolean', example: false, nullable: true)]
    private ?bool $isLastNameChanged = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Предыдущее ФИО должника (если изменялось)', type: 'string', example: 'Петров Петр Петрович', nullable: true)]
    private ?string $changedLastName = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Дата рождения должника', type: 'string', format: 'date', example: '1990-01-01', nullable: true)]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'СНИЛС должника', type: 'string', example: '123-456-789 00', nullable: true)]
    private ?string $snils = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Место рождения должника', type: 'string', example: 'г. Москва', nullable: true)]
    private ?string $birthPlace = null;

    // Адрес регистрации (отдельные поля)
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Субъект РФ (регион)', type: 'string', example: 'Санкт-Петербург', nullable: true)]
    private ?string $registrationRegion = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Район', type: 'string', example: 'Московский', nullable: true)]
    private ?string $registrationDistrict = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Город', type: 'string', example: 'Санкт-Петербург', nullable: true)]
    private ?string $registrationCity = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Населенный пункт', type: 'string', example: 'пос. Ленинский', nullable: true)]
    private ?string $registrationSettlement = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Улица', type: 'string', example: 'Смоленская', nullable: true)]
    private ?string $registrationStreet = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Дом', type: 'string', example: '9', nullable: true)]
    private ?string $registrationHouse = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Корпус', type: 'string', example: '1', nullable: true)]
    private ?string $registrationBuilding = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Квартира', type: 'string', example: '418', nullable: true)]
    private ?string $registrationApartment = null;

    // Паспорт
    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Серия паспорта', type: 'string', example: '4016', nullable: true)]
    private ?string $passportSeries = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Номер паспорта', type: 'string', example: '123456', nullable: true)]
    private ?string $passportNumber = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Кем выдан паспорт', type: 'string', example: 'ОУФМС России по СПб и ЛО в Московском районе', nullable: true)]
    private ?string $passportIssuedBy = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Дата выдачи паспорта', type: 'string', format: 'date', example: '2010-05-15', nullable: true)]
    private ?\DateTimeInterface $passportIssuedDate = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Код подразделения', type: 'string', example: '780-089', nullable: true)]
    private ?string $passportDepartmentCode = null;

    // Семейное положение
    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Семейное положение (Да/Нет/Не состоял в течение 3 лет)', type: 'string', example: 'Да', nullable: true)]
    private ?string $maritalStatus = null;

    // Данные супруга
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'ФИО супруга', type: 'string', example: 'Иванова Мария Петровна', nullable: true)]
    private ?string $spouseFullName = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Дата рождения супруга', type: 'string', format: 'date', example: '1992-03-25', nullable: true)]
    private ?\DateTimeInterface $spouseBirthDate = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Наличие несовершеннолетних детей', type: 'boolean', example: true, nullable: true)]
    private ?bool $hasMinorChildren = null;

    /**
     * @var array<int, array{firstName: string, lastName: string, middleName: ?string, isLastNameChanged: bool, changedLastName: ?string, birthDate: string}>|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(
        description: 'Список несовершеннолетних детей',
        type: 'array',
        items: new OA\Items(
            properties: [
                new OA\Property(property: 'firstName', type: 'string', example: 'Александр'),
                new OA\Property(property: 'lastName', type: 'string', example: 'Иванов'),
                new OA\Property(property: 'middleName', type: 'string', example: 'Иванович', nullable: true),
                new OA\Property(property: 'isLastNameChanged', type: 'boolean', example: false),
                new OA\Property(property: 'changedLastName', type: 'string', example: null, nullable: true),
                new OA\Property(property: 'birthDate', type: 'string', format: 'date', example: '2015-08-10'),
            ],
            type: 'object'
        ),
        nullable: true
    )]
    private ?array $children = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Является ли студентом', type: 'boolean', example: false, nullable: true)]
    private ?bool $isStudent = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Наименование работодателя', type: 'string', example: 'ООО "Рога и Копыта"', nullable: true)]
    private ?string $employerName = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Адрес работодателя', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true)]
    private ?string $employerAddress = null;

    #[ORM\Column(length: 12, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'ИНН работодателя', type: 'string', example: '1234567890', nullable: true)]
    private ?string $employerInn = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Пенсии и социальные выплаты (алименты, пособия, ЕДВ, прочее)', type: 'string', example: 'Пособие по безработице, ЕДВ', nullable: true)]
    private ?string $socialBenefits = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Телефон', type: 'string', example: '+7 (999) 123-45-67', nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Электронная почта', type: 'string', format: 'email', example: 'example@mail.ru', nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Адрес для направления корреспонденции', type: 'string', example: '196084, г. Санкт-Петербург, ул. Смоленская, 9-418', nullable: true)]
    private ?string $mailingAddress = null;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Сумма долга', type: 'number', format: 'decimal', example: 1500000.50, nullable: true)]
    private ?string $debtAmount = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Наличие возбужденных исполнительных производств', type: 'boolean', example: true, nullable: true)]
    private ?bool $hasEnforcementProceedings = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'contracts')]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $author;

    #[ORM\Column(type: 'string', nullable: false, enumType: ContractStatus::class)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Статус договора', type: 'string', enum: ContractStatus::class, example: 'in_progress')]
    private ContractStatus $status = ContractStatus::IN_PROGRESS;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'manager_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Управляющий', type: 'object', nullable: true)]
    private ?User $manager = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Номер договора', type: 'string', example: 'ДГ-2024-001', nullable: true)]
    private ?string $contractNumber = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['basic_info'])]
    #[OA\Property(description: 'Дата договора', type: 'string', format: 'date', example: '2024-01-15', nullable: true)]
    private ?\DateTimeInterface $contractDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(?string $middleName): self
    {
        $this->middleName = $middleName;

        return $this;
    }

    public function isLastNameChanged(): ?bool
    {
        return $this->isLastNameChanged;
    }

    public function setIsLastNameChanged(?bool $isLastNameChanged): self
    {
        $this->isLastNameChanged = $isLastNameChanged;

        return $this;
    }

    public function getChangedLastName(): ?string
    {
        return $this->changedLastName;
    }

    public function setChangedLastName(?string $changedLastName): self
    {
        $this->changedLastName = $changedLastName;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

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

    public function getBirthPlace(): ?string
    {
        return $this->birthPlace;
    }

    public function setBirthPlace(?string $birthPlace): self
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getFullName(): ?string
    {
        $parts = array_filter([
            $this->lastName,
            $this->firstName,
            $this->middleName,
        ]);

        return $parts ? implode(' ', $parts) : null;
    }

    public function getRegistrationRegion(): ?string
    {
        return $this->registrationRegion;
    }

    public function setRegistrationRegion(?string $registrationRegion): self
    {
        $this->registrationRegion = $registrationRegion;

        return $this;
    }

    public function getRegistrationDistrict(): ?string
    {
        return $this->registrationDistrict;
    }

    public function setRegistrationDistrict(?string $registrationDistrict): self
    {
        $this->registrationDistrict = $registrationDistrict;

        return $this;
    }

    public function getRegistrationCity(): ?string
    {
        return $this->registrationCity;
    }

    public function setRegistrationCity(?string $registrationCity): self
    {
        $this->registrationCity = $registrationCity;

        return $this;
    }

    public function getRegistrationSettlement(): ?string
    {
        return $this->registrationSettlement;
    }

    public function setRegistrationSettlement(?string $registrationSettlement): self
    {
        $this->registrationSettlement = $registrationSettlement;

        return $this;
    }

    public function getRegistrationStreet(): ?string
    {
        return $this->registrationStreet;
    }

    public function setRegistrationStreet(?string $registrationStreet): self
    {
        $this->registrationStreet = $registrationStreet;

        return $this;
    }

    public function getRegistrationHouse(): ?string
    {
        return $this->registrationHouse;
    }

    public function setRegistrationHouse(?string $registrationHouse): self
    {
        $this->registrationHouse = $registrationHouse;

        return $this;
    }

    public function getRegistrationBuilding(): ?string
    {
        return $this->registrationBuilding;
    }

    public function setRegistrationBuilding(?string $registrationBuilding): self
    {
        $this->registrationBuilding = $registrationBuilding;

        return $this;
    }

    public function getRegistrationApartment(): ?string
    {
        return $this->registrationApartment;
    }

    public function setRegistrationApartment(?string $registrationApartment): self
    {
        $this->registrationApartment = $registrationApartment;

        return $this;
    }

    public function getFullRegistrationAddress(): ?string
    {
        $parts = [];

        if ($this->registrationRegion) {
            $parts[] = $this->registrationRegion;
        }
        if ($this->registrationDistrict) {
            $parts[] = $this->registrationDistrict . ' район';
        }
        if ($this->registrationCity) {
            $parts[] = 'г. ' . $this->registrationCity;
        }
        if ($this->registrationSettlement) {
            $parts[] = $this->registrationSettlement;
        }
        if ($this->registrationStreet) {
            $parts[] = 'ул. ' . $this->registrationStreet;
        }
        if ($this->registrationHouse) {
            $parts[] = 'д. ' . $this->registrationHouse;
        }
        if ($this->registrationBuilding) {
            $parts[] = 'корп. ' . $this->registrationBuilding;
        }
        if ($this->registrationApartment) {
            $parts[] = 'кв. ' . $this->registrationApartment;
        }

        return $parts ? implode(', ', $parts) : null;
    }

    public function getPassportSeries(): ?string
    {
        return $this->passportSeries;
    }

    public function setPassportSeries(?string $passportSeries): self
    {
        $this->passportSeries = $passportSeries;

        return $this;
    }

    public function getPassportNumber(): ?string
    {
        return $this->passportNumber;
    }

    public function setPassportNumber(?string $passportNumber): self
    {
        $this->passportNumber = $passportNumber;

        return $this;
    }

    public function getPassportIssuedBy(): ?string
    {
        return $this->passportIssuedBy;
    }

    public function setPassportIssuedBy(?string $passportIssuedBy): self
    {
        $this->passportIssuedBy = $passportIssuedBy;

        return $this;
    }

    public function getPassportIssuedDate(): ?\DateTimeInterface
    {
        return $this->passportIssuedDate;
    }

    public function setPassportIssuedDate(?\DateTimeInterface $passportIssuedDate): self
    {
        $this->passportIssuedDate = $passportIssuedDate;

        return $this;
    }

    public function getPassportDepartmentCode(): ?string
    {
        return $this->passportDepartmentCode;
    }

    public function setPassportDepartmentCode(?string $passportDepartmentCode): self
    {
        $this->passportDepartmentCode = $passportDepartmentCode;

        return $this;
    }

    public function getMaritalStatus(): ?string
    {
        return $this->maritalStatus;
    }

    public function setMaritalStatus(?string $maritalStatus): self
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    public function getSpouseFullName(): ?string
    {
        return $this->spouseFullName;
    }

    public function setSpouseFullName(?string $spouseFullName): self
    {
        $this->spouseFullName = $spouseFullName;

        return $this;
    }

    public function getSpouseBirthDate(): ?\DateTimeInterface
    {
        return $this->spouseBirthDate;
    }

    public function setSpouseBirthDate(?\DateTimeInterface $spouseBirthDate): self
    {
        $this->spouseBirthDate = $spouseBirthDate;

        return $this;
    }

    public function hasMinorChildren(): ?bool
    {
        return $this->hasMinorChildren;
    }

    public function setHasMinorChildren(?bool $hasMinorChildren): self
    {
        $this->hasMinorChildren = $hasMinorChildren;

        return $this;
    }

    /**
     * @return array<int, array{firstName: string, lastName: string, middleName: ?string, isLastNameChanged: bool, changedLastName: ?string, birthDate: string}>|null
     */
    public function getChildren(): ?array
    {
        return $this->children;
    }

    /**
     * @param array<int, array{firstName: string, lastName: string, middleName: ?string, isLastNameChanged: bool, changedLastName: ?string, birthDate: string}>|null $children
     */
    public function setChildren(?array $children): self
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @param array{firstName: string, lastName: string, middleName: ?string, isLastNameChanged: bool, changedLastName: ?string, birthDate: string} $child
     */
    public function addChild(array $child): self
    {
        if (null === $this->children) {
            $this->children = [];
        }

        $this->children[] = $child;

        return $this;
    }

    public function isStudent(): ?bool
    {
        return $this->isStudent;
    }

    public function setIsStudent(?bool $isStudent): self
    {
        $this->isStudent = $isStudent;

        return $this;
    }

    public function getEmployerName(): ?string
    {
        return $this->employerName;
    }

    public function setEmployerName(?string $employerName): self
    {
        $this->employerName = $employerName;

        return $this;
    }

    public function getEmployerAddress(): ?string
    {
        return $this->employerAddress;
    }

    public function setEmployerAddress(?string $employerAddress): self
    {
        $this->employerAddress = $employerAddress;

        return $this;
    }

    public function getEmployerInn(): ?string
    {
        return $this->employerInn;
    }

    public function setEmployerInn(?string $employerInn): self
    {
        $this->employerInn = $employerInn;

        return $this;
    }

    public function getSocialBenefits(): ?string
    {
        return $this->socialBenefits;
    }

    public function setSocialBenefits(?string $socialBenefits): self
    {
        $this->socialBenefits = $socialBenefits;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getMailingAddress(): ?string
    {
        return $this->mailingAddress;
    }

    public function setMailingAddress(?string $mailingAddress): self
    {
        $this->mailingAddress = $mailingAddress;

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

    public function hasEnforcementProceedings(): ?bool
    {
        return $this->hasEnforcementProceedings;
    }

    public function setHasEnforcementProceedings(?bool $hasEnforcementProceedings): self
    {
        $this->hasEnforcementProceedings = $hasEnforcementProceedings;

        return $this;
    }

    public function getStatus(): ContractStatus
    {
        return $this->status;
    }

    public function setStatus(ContractStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function getContractNumber(): ?string
    {
        return $this->contractNumber;
    }

    public function setContractNumber(?string $contractNumber): self
    {
        $this->contractNumber = $contractNumber;

        return $this;
    }

    public function getContractDate(): ?\DateTimeInterface
    {
        return $this->contractDate;
    }

    public function setContractDate(?\DateTimeInterface $contractDate): self
    {
        $this->contractDate = $contractDate;

        return $this;
    }
}
