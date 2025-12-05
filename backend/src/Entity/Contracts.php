<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\BankruptcyStage;
use App\Entity\Enum\ContractStatus;
use App\Repository\ContractsRepository;
use App\Service\Templates\PreCourt\PreCourtMethods;
use App\Service\Templates\ProcedureInitiation\ProcedureInitiationMethods;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ContractsRepository::class)]
#[OA\Schema(
    schema: 'Contracts',
    type: 'object'
)]
class Contracts extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'ID контракта', type: 'integer', example: 1)]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Имя должника', type: Types::STRING, example: 'Иван', nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Фамилия должника', type: Types::STRING, example: 'Иванов', nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Отчество должника', type: Types::STRING, example: 'Иванович', nullable: true)]
    private ?string $middleName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Имя должника в родительном падеже', type: Types::STRING, example: 'Ивана', nullable: true)]
    private ?string $firstNameGenitive = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Фамилия должника в родительном падеже', type: Types::STRING, example: 'Иванова', nullable: true)]
    private ?string $lastNameGenitive = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Отчество должника в родительном падеже', type: Types::STRING, example: 'Ивановича', nullable: true)]
    private ?string $middleNameGenitive = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Изменялось ли ФИО должника', type: 'boolean', example: false, nullable: true)]
    private ?bool $isLastNameChanged = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Пол', type: Types::STRING, example: false, nullable: true)]
    private ?string $gender = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Предыдущее ФИО должника (если изменялось)', type: Types::STRING, example: 'Петров Петр Петрович', nullable: true)]
    private ?string $changedLastName = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Дата рождения должника', type: Types::STRING, format: 'date', example: '1990-01-01', nullable: true)]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'СНИЛС должника', type: Types::STRING, example: '123-456-789 00', nullable: true)]
    private ?string $snils = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Место рождения должника', type: Types::STRING, example: 'г. Москва', nullable: true)]
    private ?string $birthPlace = null;

    #[ORM\Column(length: 2000, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Фактическое место проживания', type: Types::STRING, example: 'Воронеж, Район 20 район, г. Воронеж, ул. Центральная, д. 69, кв. 257', nullable: true)]
    private ?string $actualPlaceResidence = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Субъект РФ (регион)', type: Types::STRING, example: 'Санкт-Петербург', nullable: true)]
    private ?string $registrationRegion = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Район', type: Types::STRING, example: 'Московский', nullable: true)]
    private ?string $registrationDistrict = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Город', type: Types::STRING, example: 'Санкт-Петербург', nullable: true)]
    private ?string $registrationCity = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Населенный пункт', type: Types::STRING, example: 'пос. Ленинский', nullable: true)]
    private ?string $registrationSettlement = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Улица', type: Types::STRING, example: 'Смоленская', nullable: true)]
    private ?string $registrationStreet = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Дом', type: Types::STRING, example: '9', nullable: true)]
    private ?string $registrationHouse = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Корпус', type: Types::STRING, example: '1', nullable: true)]
    private ?string $registrationBuilding = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Квартира', type: Types::STRING, example: '418', nullable: true)]
    private ?string $registrationApartment = null;

    // Паспорт
    #[ORM\Column(length: 10, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Серия паспорта', type: Types::STRING, example: '4016', nullable: true)]
    private ?string $passportSeries = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Номер паспорта', type: Types::STRING, example: '123456', nullable: true)]
    private ?string $passportNumber = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Кем выдан паспорт', type: Types::STRING, example: 'ОУФМС России по СПб и ЛО в Московском районе', nullable: true)]
    private ?string $passportIssuedBy = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Дата выдачи паспорта', type: Types::STRING, format: 'date', example: '2010-05-15', nullable: true)]
    private ?\DateTimeInterface $passportIssuedDate = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Код подразделения', type: Types::STRING, example: '780-089', nullable: true)]
    private ?string $passportDepartmentCode = null;

    // Семейное положение
    #[ORM\Column(length: 50, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Семейное положение (Да/Нет/Не состоял в течение 3 лет)', type: Types::STRING, example: 'Да', nullable: true)]
    private ?string $maritalStatus = null;

    // Данные супруга
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'ФИО супруга', type: Types::STRING, example: 'Иванова Мария Петровна', nullable: true)]
    private ?string $spouseFullName = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Дата рождения супруга', type: Types::STRING, format: 'date', example: '1992-03-25', nullable: true)]
    private ?\DateTimeInterface $spouseBirthDate = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Наличие несовершеннолетних детей', type: 'boolean', example: true, nullable: true)]
    private ?bool $hasMinorChildren = null;

    /**
     * @var array<int, array{firstName: string, lastName: string, middleName: ?string, isLastNameChanged: bool, changedLastName: ?string, birthDate: string}>|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(
        description: 'Список несовершеннолетних детей',
        type: 'array',
        items: new OA\Items(
            properties: [
                new OA\Property(property: 'firstName', type: Types::STRING, example: 'Александр'),
                new OA\Property(property: 'lastName', type: Types::STRING, example: 'Иванов'),
                new OA\Property(property: 'middleName', type: Types::STRING, example: 'Иванович', nullable: true),
                new OA\Property(property: 'isLastNameChanged', type: 'boolean', example: false),
                new OA\Property(property: 'changedLastName', type: Types::STRING, example: null, nullable: true),
                new OA\Property(property: 'birthDate', type: Types::STRING, format: 'date', example: '2015-08-10'),
            ],
            type: 'object'
        ),
        nullable: true
    )]
    private ?array $children = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Является ли студентом', type: 'boolean', example: false, nullable: true)]
    private ?bool $isStudent = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Наименование работодателя', type: Types::STRING, example: 'ООО "Рога и Копыта"', nullable: true)]
    private ?string $employerName = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Адрес работодателя', type: Types::STRING, example: 'г. Москва, ул. Ленина, д. 1', nullable: true)]
    private ?string $employerAddress = null;

    #[ORM\Column(length: 12, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'ИНН работодателя', type: Types::STRING, example: '1234567890', nullable: true)]
    private ?string $employerInn = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Пенсии и социальные выплаты (алименты, пособия, ЕДВ, прочее)', type: Types::STRING, example: 'Пособие по безработице, ЕДВ', nullable: true)]
    private ?string $socialBenefits = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Есть работа', type: 'boolean', example: false, nullable: true)]
    private ?bool $work = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Телефон', type: Types::STRING, example: '+7 (999) 123-45-67', nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Электронная почта', type: Types::STRING, format: 'email', example: 'example@mail.ru', nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Адрес для направления корреспонденции', type: Types::STRING, example: '196084, г. Санкт-Петербург, ул. Смоленская, 9-418', nullable: true)]
    private ?string $mailingAddress = null;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Сумма долга', type: 'number', format: 'decimal', example: 1500000.50, nullable: true)]
    private ?string $debtAmount = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Наличие возбужденных исполнительных производств', type: 'boolean', example: true, nullable: true)]
    private ?bool $hasEnforcementProceedings = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'contracts')]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $author;

    #[ORM\Column(type: Types::STRING, nullable: false, enumType: ContractStatus::class)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Статус договора', type: Types::STRING, enum: ContractStatus::class, example: 'in_progress')]
    private ContractStatus $status = ContractStatus::IN_PROGRESS;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'manager_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Управляющий', type: 'object', nullable: true)]
    private ?User $manager = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Номер договора', type: Types::STRING, example: 'ДГ-2024-001', nullable: true)]
    private ?string $contractNumber = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Дата договора', type: Types::STRING, format: 'date', example: '2024-01-15', nullable: true)]
    private ?\DateTimeInterface $contractDate = null;

    #[ORM\OneToOne(targetEntity: Court::class)]
    #[ORM\JoinColumn(name: 'court_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: 'Арбитражный суд', type: 'object', nullable: true)]
    private ?Court $court = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: 'Номер доверенности', type: Types::STRING, example: 'Д-123/2024', nullable: true)]
    private ?string $powerOfAttorneyNumber = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: 'Дата составления доверенности', type: Types::STRING, format: 'date', example: '2024-01-15', nullable: true)]
    private ?\DateTimeInterface $powerOfAttorneyDate = null;

    /**
     * @var Collection<int, Creditor>
     */
    #[ORM\ManyToMany(targetEntity: Creditor::class)]
    #[ORM\JoinTable(name: 'contracts_creditors')]
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: 'Кредиторы', type: 'array', items: new OA\Items(type: 'object'), nullable: true)]
    private Collection $creditors;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: '№ Дела', type: Types::STRING, example: 'А56-75258/2025', nullable: true)]
    private ?string $caseNumber = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: 'Дата и время заседания', type: Types::STRING, format: 'date-time', example: '2025-01-15T10:00:00', nullable: true)]
    private ?\DateTimeInterface $hearingDateTime = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: 'Дата и время ЕФРСБ', type: Types::STRING, format: 'date-time', example: '2025-01-15T14:00:00', nullable: true)]
    private ?\DateTimeInterface $efrsbDateTime = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::PRE_COURT->value])]
    #[OA\Property(description: 'Кабинет ЕФРСБ', type: Types::STRING, example: 'Кабинет 101', nullable: true)]
    private ?string $efrsbCabinet = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Почтовый индекс', type: Types::STRING, example: '123123', nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups([BankruptcyStage::BASIC_INFO->value])]
    #[OA\Property(description: 'Дата расторжения брака', type: Types::STRING, format: 'date-time', example: '2025-01-15T14:00:00', nullable: true)]
    private ?\DateTimeInterface $marriageTerminationDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    #[OA\Property(
        description: 'Дата принятия судебного решения',
        type: Types::STRING,
        format: 'date',
        example: '2025-06-20',
        nullable: true
    )]
    private ?\DateTimeInterface $procedureInitiationDecisionDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    #[OA\Property(
        description: 'Дата объявления резолютивной части судебного решения',
        type: Types::STRING,
        format: 'date',
        example: '2025-06-20',
        nullable: true
    )]
    private ?\DateTimeInterface $procedureInitiationResolutionDate = null;

    #[ORM\ManyToOne(targetEntity: Mchs::class)]
    #[ORM\JoinColumn(name: 'procedure_initiation_mchs_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    #[OA\Property(description: 'ГИМС', type: 'object', nullable: true)]
    private ?Mchs $procedureInitiationMchs = null;

    #[ORM\ManyToOne(targetEntity: Gostekhnadzor::class)]
    #[ORM\JoinColumn(name: 'procedure_initiation_gostekhnadzor_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    #[OA\Property(description: 'Гостехнадзор', type: 'object', nullable: true)]
    private ?Gostekhnadzor $procedureInitiationGostekhnadzor = null;

    #[ORM\ManyToOne(targetEntity: Fns::class)]
    #[ORM\JoinColumn(name: 'procedure_initiation_fns_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    #[OA\Property(description: 'ФНС', type: 'object', nullable: true)]
    private ?Fns $procedureInitiationFns = null;

    #[ORM\ManyToOne(targetEntity: Bailiff::class)]
    #[ORM\JoinColumn(name: 'procedure_initiation_bailiff_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    #[OA\Property(description: 'Судебный пристав', type: 'object', nullable: true)]
    private ?Bailiff $procedureInitiationBailiff = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    #[OA\Property(
        description: 'Дата документа',
        type: Types::STRING,
        format: 'date',
        example: '2025-06-20',
        nullable: true
    )]
    private ?\DateTimeInterface $procedureInitiationDocumentDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    #[OA\Property(description: 'Номер дела', type: Types::STRING, example: 'А56-12345/2025', nullable: true)]
    private ?string $procedureInitiationCaseNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    #[OA\Property(description: 'Судья', type: Types::STRING, example: 'Иванов Иван Иванович', nullable: true)]
    private ?string $procedureInitiationJudge = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    #[OA\Property(description: 'Номер спец счёта', type: Types::STRING, example: '40817810099910004312', nullable: true)]
    private ?string $procedureInitiationSpecialAccountNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    #[OA\Property(description: 'Продолжительность процедуры', type: Types::STRING, example: 'шесть месяцев', nullable: true)]
    private ?string $procedureInitiationDuration = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    #[OA\Property(description: 'Номер документа', type: Types::STRING, example: 'Исх. № 236 от «04» июля 2025 г.', nullable: true)]
    private ?string $procedureInitiationDocNumber = null;

    #[ORM\ManyToOne(targetEntity: Rosgvardia::class)]
    #[ORM\JoinColumn(name: 'procedure_initiation_rosgvardia_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    #[OA\Property(description: 'Росгвардия', type: 'object', nullable: true)]
    private ?Rosgvardia $procedureInitiationRosgvardia = null;

    /**
     * @var array<int, array{number: string, date: string}>|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]
    #[OA\Property(
        description: 'Окончания исполнительных производств',
        type: 'array',
        items: new OA\Items(
            properties: [
                new OA\Property(property: 'number', type: Types::STRING, example: '199465/22/05023-ИП'),
                new OA\Property(property: 'date', type: Types::STRING, format: 'date', example: '2024-01-15'),
            ],
            type: 'object'
        ),
        nullable: true
    )]
    private ?array $procedureInitiationIPEndings = null;

    public function __construct()
    {
        parent::__construct();
        $this->creditors = new ArrayCollection();
    }

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

    public function getFirstNameGenitive(): ?string
    {
        return $this->firstNameGenitive;
    }

    public function setFirstNameGenitive(?string $firstNameGenitive): self
    {
        $this->firstNameGenitive = $firstNameGenitive;

        return $this;
    }

    public function getLastNameGenitive(): ?string
    {
        return $this->lastNameGenitive;
    }

    public function setLastNameGenitive(?string $lastNameGenitive): self
    {
        $this->lastNameGenitive = $lastNameGenitive;

        return $this;
    }

    public function getMiddleNameGenitive(): ?string
    {
        return $this->middleNameGenitive;
    }

    public function setMiddleNameGenitive(?string $middleNameGenitive): self
    {
        $this->middleNameGenitive = $middleNameGenitive;

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

    public function getShortFullName(): ?string
    {
        if (!$this->lastName) {
            return null;
        }

        $initials = [];

        if ($this->firstName) {
            $initials[] = mb_substr($this->firstName, 0, 1) . '.';
        }
        if ($this->middleName) {
            $initials[] = mb_substr($this->middleName, 0, 1) . '.';
        }

        $result = $this->lastName;

        if ($initials) {
            $result .= ' ' . implode('', $initials);
        }

        return $result;
    }

    public function getFullNameGenitive(): ?string
    {
        $parts = array_filter([
            $this->lastNameGenitive,
            $this->firstNameGenitive,
            $this->middleNameGenitive,
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

    public function getCourt(): ?Court
    {
        return $this->court;
    }

    public function setCourt(?Court $court): self
    {
        $this->court = $court;

        return $this;
    }

    public function getProcedureInitiationMchs(): ?Mchs
    {
        return $this->procedureInitiationMchs;
    }

    public function setProcedureInitiationMchs(?Mchs $procedureInitiationMchs): self
    {
        $this->procedureInitiationMchs = $procedureInitiationMchs;

        return $this;
    }

    public function getProcedureInitiationGostekhnadzor(): ?Gostekhnadzor
    {
        return $this->procedureInitiationGostekhnadzor;
    }

    public function setProcedureInitiationGostekhnadzor(?Gostekhnadzor $procedureInitiationGostekhnadzor): self
    {
        $this->procedureInitiationGostekhnadzor = $procedureInitiationGostekhnadzor;

        return $this;
    }

    public function getProcedureInitiationFns(): ?Fns
    {
        return $this->procedureInitiationFns;
    }

    public function setProcedureInitiationFns(?Fns $procedureInitiationFns): self
    {
        $this->procedureInitiationFns = $procedureInitiationFns;

        return $this;
    }

    public function getProcedureInitiationBailiff(): ?Bailiff
    {
        return $this->procedureInitiationBailiff;
    }

    public function setProcedureInitiationBailiff(?Bailiff $procedureInitiationBailiff): self
    {
        $this->procedureInitiationBailiff = $procedureInitiationBailiff;

        return $this;
    }

    public function getProcedureInitiationDocumentDate(): ?\DateTimeInterface
    {
        return $this->procedureInitiationDocumentDate;
    }

    public function setProcedureInitiationDocumentDate(?\DateTimeInterface $procedureInitiationDocumentDate): self
    {
        $this->procedureInitiationDocumentDate = $procedureInitiationDocumentDate;

        return $this;
    }

    public function getProcedureInitiationCaseNumber(): ?string
    {
        return $this->procedureInitiationCaseNumber;
    }

    public function setProcedureInitiationCaseNumber(?string $procedureInitiationCaseNumber): self
    {
        $this->procedureInitiationCaseNumber = $procedureInitiationCaseNumber;

        return $this;
    }

    public function getProcedureInitiationJudge(): ?string
    {
        return $this->procedureInitiationJudge;
    }

    public function setProcedureInitiationJudge(?string $procedureInitiationJudge): self
    {
        $this->procedureInitiationJudge = $procedureInitiationJudge;

        return $this;
    }

    public function getProcedureInitiationSpecialAccountNumber(): ?string
    {
        return $this->procedureInitiationSpecialAccountNumber;
    }

    public function setProcedureInitiationSpecialAccountNumber(?string $procedureInitiationSpecialAccountNumber): self
    {
        $this->procedureInitiationSpecialAccountNumber = $procedureInitiationSpecialAccountNumber;

        return $this;
    }

    public function getPowerOfAttorneyNumber(): ?string
    {
        return $this->powerOfAttorneyNumber;
    }

    public function setPowerOfAttorneyNumber(?string $powerOfAttorneyNumber): self
    {
        $this->powerOfAttorneyNumber = $powerOfAttorneyNumber;

        return $this;
    }

    public function getPowerOfAttorneyDate(): ?\DateTimeInterface
    {
        return $this->powerOfAttorneyDate;
    }

    public function setPowerOfAttorneyDate(?\DateTimeInterface $powerOfAttorneyDate): self
    {
        $this->powerOfAttorneyDate = $powerOfAttorneyDate;

        return $this;
    }

    /**
     * @return Collection<int, Creditor>
     */
    public function getCreditors(): Collection
    {
        return $this->creditors;
    }

    public function addCreditor(Creditor $creditor): self
    {
        if (!$this->creditors->contains($creditor)) {
            $this->creditors->add($creditor);
        }

        return $this;
    }

    public function removeCreditor(Creditor $creditor): self
    {
        $this->creditors->removeElement($creditor);

        return $this;
    }

    /**
     * @return array<int, array{number: string, date: string}>|null
     */
    public function getProcedureInitiationIPEndings(): ?array
    {
        return $this->procedureInitiationIPEndings;
    }

    /**
     * @param array<int, array{number: string, date: string}>|null $procedureInitiationIPEndings
     */
    public function setProcedureInitiationIPEndings(?array $procedureInitiationIPEndings): self
    {
        $this->procedureInitiationIPEndings = $procedureInitiationIPEndings;

        return $this;
    }

    /**
     * @param array{number: string, date: string} $ipEnding
     */
    public function addIpEnding(array $ipEnding): self
    {
        if (null === $this->procedureInitiationIPEndings) {
            $this->procedureInitiationIPEndings = [];
        }

        $this->procedureInitiationIPEndings[] = $ipEnding;

        return $this;
    }

    public function IPEndingsStr(): string
    {
        return ProcedureInitiationMethods::IPEndingsStr(contract: $this);
    }

    public function getCaseNumber(): ?string
    {
        return $this->caseNumber;
    }

    public function setCaseNumber(?string $caseNumber): self
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    public function getHearingDateTime(): ?\DateTimeInterface
    {
        return $this->hearingDateTime;
    }

    public function setHearingDateTime(?\DateTimeInterface $hearingDateTime): self
    {
        $this->hearingDateTime = $hearingDateTime;

        return $this;
    }

    public function getEfrsbDateTime(): ?\DateTimeInterface
    {
        return $this->efrsbDateTime;
    }

    public function setEfrsbDateTime(?\DateTimeInterface $efrsbDateTime): self
    {
        $this->efrsbDateTime = $efrsbDateTime;

        return $this;
    }

    public function getProcedureInitiationDecisionDate(): ?\DateTimeInterface
    {
        return $this->procedureInitiationDecisionDate;
    }

    public function setProcedureInitiationDecisionDate(?\DateTimeInterface $procedureInitiationDecisionDate): self
    {
        $this->procedureInitiationDecisionDate = $procedureInitiationDecisionDate;

        return $this;
    }

    public function getProcedureInitiationResolutionDate(): ?\DateTimeInterface
    {
        return $this->procedureInitiationResolutionDate;
    }

    public function setProcedureInitiationResolutionDate(?\DateTimeInterface $procedureInitiationResolutionDate): self
    {
        $this->procedureInitiationResolutionDate = $procedureInitiationResolutionDate;

        return $this;
    }

    public function getEfrsbCabinet(): ?string
    {
        return $this->efrsbCabinet;
    }

    public function setEfrsbCabinet(?string $efrsbCabinet): self
    {
        $this->efrsbCabinet = $efrsbCabinet;

        return $this;
    }

    public function getWork(): ?bool
    {
        return $this->work;
    }

    public function setWork(?bool $work): self
    {
        $this->work = $work;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getMarriageTerminationDate(): ?\DateTimeInterface
    {
        return $this->marriageTerminationDate;
    }

    public function setMarriageTerminationDate(?\DateTimeInterface $marriageTerminationDate): self
    {
        $this->marriageTerminationDate = $marriageTerminationDate;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getProcedureInitiationDuration(): ?string
    {
        return $this->procedureInitiationDuration;
    }

    public function setProcedureInitiationDuration(?string $procedureInitiationDuration): self
    {
        $this->procedureInitiationDuration = $procedureInitiationDuration;

        return $this;
    }

    public function getProcedureInitiationDocNumber(): ?string
    {
        return $this->procedureInitiationDocNumber;
    }

    public function setProcedureInitiationDocNumber(?string $procedureInitiationDocNumber): self
    {
        $this->procedureInitiationDocNumber = $procedureInitiationDocNumber;

        return $this;
    }

    public function getActualPlaceResidence(): ?string
    {
        return $this->actualPlaceResidence;
    }

    public function setActualPlaceResidence(?string $actualPlaceResidence): self
    {
        $this->actualPlaceResidence = $actualPlaceResidence;

        return $this;
    }

    public function getTemplateWork(): string
    {
        if (!$this->work || empty($this->employerName) || empty($this->employerAddress) || empty($this->employerInn)) {
            return 'не работает';
        }

        return $this->employerName . ', ' . $this->employerAddress . ', ' . $this->employerInn;
    }

    public function getCurrentYear(): int
    {
        return (int)date('Y');
    }

    public function getMaritalStatusDescription(): string
    {
        return PreCourtMethods::maritalStatusDescription(contract: $this);
    }

    public function getWorkDescription(): string
    {
        return PreCourtMethods::workDescription(contract: $this);
    }

    public function getHasEnforcementProceedingsDescription(): string
    {
        return PreCourtMethods::hasEnforcementProceedingsDescription(contract: $this);
    }

    public function getFinancialManagerReportHearingDescription(): string
    {
        return ProcedureInitiationMethods::financialManagerReportHearingDescription(contract: $this);
    }

    public function getProcedureInitiationDateForPublication(): string
    {
        return ProcedureInitiationMethods::procedureInitiationDateForPublication(contract: $this);
    }

    public function getProcedureInitiationDateWithResolution(): string
    {
        return ProcedureInitiationMethods::procedureInitiationDateWithResolution(contract: $this);
    }

    public function getEfrsbHearingInfo(): string
    {
        return ProcedureInitiationMethods::efrsbHearingInfo(contract: $this);
    }

    public function getStartNotificationText(): string
    {
        return ProcedureInitiationMethods::startNotificationText(contract: $this);
    }

    public function getResolutionPart(): string
    {
        return ProcedureInitiationMethods::resolutionPart(contract: $this);
    }

    public function getSpouse(): string
    {
        return ProcedureInitiationMethods::spouse(contract: $this);
    }

    public function getSpouseExtended(): string
    {
        return ProcedureInitiationMethods::spouseExtended(contract: $this);
    }

    public function getPhoneAndEmail(): string
    {
        $parts = [];

        if ($this->phone !== null && $this->phone !== '') {
            $parts[] = $this->phone;
        }

        if ($this->email !== null && $this->email !== '') {
            $parts[] = $this->email;
        }

        return implode(', ', $parts);
    }

    public function getProcedureInitiationRosgvardia(): ?Rosgvardia
    {
        return $this->procedureInitiationRosgvardia;
    }

    public function setProcedureInitiationRosgvardia(?Rosgvardia $procedureInitiationRosgvardia): self
    {
        $this->procedureInitiationRosgvardia = $procedureInitiationRosgvardia;

        return $this;
    }
}
