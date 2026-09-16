<?php

namespace App\Service;

use App\Entity\Contracts;
use App\Entity\ContractsCreditorsClaim;
use App\Entity\ContractsPreCourtCreditor;
use App\Entity\ContractsProperty;
use App\Entity\Enum\BankruptcyStage;
use App\Entity\Enum\ContractStatus;
use App\Entity\Enum\ProcedureExtensionStatus;
use App\Entity\Enum\PropertySubtype;
use App\Repository\BailiffRepository;
use App\Repository\ContractsCreditorsClaimRepository;
use App\Repository\ContractsPreCourtCreditorRepository;
use App\Repository\ContractsPropertyRepository;
use App\Repository\CourtRepository;
use App\Repository\CreditorRepository;
use App\Repository\DocumentTemplateRepository;
use App\Repository\FinancialManagerRepository;
use App\Repository\FnsRepository;
use App\Repository\GostekhnadzorRepository;
use App\Repository\MchsRepository;
use App\Repository\RosgvardiaRepository;
use App\Service\Templates\DocumentTemplateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ContractorService
{
    private const string PROCEDURE_EXTENSION_STATUS_KEY = 'procedureExtensionStatus';
    private const string PROCEDURE_EXTENSION_DATES_KEY = 'procedureExtensionDates';
    private const string ISO_DATE_FORMAT = 'Y-m-d';
    private const string CLAIM_REGISTRY_ENTRY_DATE_KEY = 'registryEntryDate';
    private const string CLAIM_OBLIGATION_TYPE_KEY = 'obligationType';
    private const string CLAIM_DISPUTE_NUMBER_KEY = 'disputeNumber';
    private const string CLAIM_ORIGIN_DATE_KEY = 'originDate';
    private const string CLAIM_REPAID_AMOUNT_KEY = 'repaidAmount';
    private const string PROPERTY_KEY = 'property';
    private const string PROPERTY_SUBTYPE_KEY = 'subtype';
    private const string PROPERTY_NAME_KEY = 'name';

    public function __construct(
        private readonly DocumentTemplateProcessor $documentTemplateProcessor,
        private readonly DocumentTemplateRepository $documentTemplateRepository,
        private readonly CourtRepository $courtRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CreditorRepository $creditorRepository,
        private readonly MchsRepository $mchsRepository,
        private readonly GostekhnadzorRepository $gostekhnadzorRepository,
        private readonly FnsRepository $fnsRepository,
        private readonly BailiffRepository $bailiffRepository,
        private readonly RosgvardiaRepository $rosgvardiaRepository,
        private readonly ContractsCreditorsClaimRepository $contractsCreditorsClaimRepository,
        private readonly ContractsPreCourtCreditorRepository $contractsPreCourtCreditorRepository,
        private readonly ContractsPropertyRepository $contractsPropertyRepository,
        private readonly FinancialManagerRepository $financialManagerRepository,
    ) {
    }

    /**
     * Сериализует контракт, группируя данные по стадиям банкротства.
     *
     * @return array<string, mixed>
     *
     * @throws ExceptionInterface
     */
    public function serializeContractByStages(Contracts $contract): array
    {
        $allTemplates = $this->documentTemplateRepository->findAll();

        $documentsByStage = [];

        foreach ($allTemplates as $template) {
            $stageValue = $template->getCategory()->value;

            if (!isset($documentsByStage[$stageValue])) {
                $documentsByStage[$stageValue] = [];
            }

            $documentsByStage[$stageValue][] = $this->documentTemplateProcessor->extractFields(
                template: $template,
                contract: $contract
            );
        }

        $result = [];

        foreach (BankruptcyStage::cases() as $stage) {
            $normalized = Serializer::normalize(data: $contract, context: ['groups' => $stage->value]);

            if (is_array($normalized)) {
                $filtered = $this->filterNullValues(data: $normalized);
                $result[$stage->value] = $filtered;
            } else {
                $result[$stage->value] = [];
            }

            if (isset($documentsByStage[$stage->value])) {
                usort($documentsByStage[$stage->value], function ($a, $b) {
                    return strnatcasecmp($a['name'], $b['name']);
                });
            }

            $result[$stage->value]['documents'] = $documentsByStage[$stage->value] ?? [];
        }

        return $result;
    }

    /**
     * Фильтрует null значения из массива рекурсивно.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function filterNullValues(array $data): array
    {
        $filtered = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_array($value)) {
                $nested = $this->filterNullValues($value);

                if (!empty($nested)) {
                    $filtered[$key] = $nested;
                }
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Обновляет поля контракта только для переданных ключей динамически.
     *
     * @param array<string, mixed> $data
     *
     * @throws \ReflectionException
     */
    public function updateContractFields(Contracts $contract, array $data): void
    {
        $reflection = new \ReflectionClass($contract);
        $dateFields = [
            'birthDate',
            'passportIssuedDate',
            'spouseBirthDate',
            'contractDate',
            'powerOfAttorneyDate',
            'courtApplicationSubmissionDate',
            'caseInitiationDate',
            'procedureInitiationDecisionDate',
            'procedureInitiationResolutionDate',
            'procedureInitiationDocumentDate',
            'procedureInitiationEfrsbMessageDate',
            'procedureInitiationKommersantPublicationDate',
            'procedureInitiationCreditorsNotificationDate',
            'propertyInventoryDate',
            'zagsCertificatePeriodFrom',
            'zagsCertificatePeriodTo',
            'bankruptcySignsEfrsbPublicationDate',
            'financialAnalysisSupplementDate',
        ];
        $dateTimeFields = [
            'hearingDateTime',
            'efrsbDateTime',
            'marriageTerminationDate',
            'procedureInitiationReportHearingDateTime',
        ];

        if (array_key_exists(self::PROCEDURE_EXTENSION_STATUS_KEY, $data) || array_key_exists(self::PROCEDURE_EXTENSION_DATES_KEY, $data)) {
            $this->updateProcedureExtension(contract: $contract, data: $data);
        }

        foreach ($data as $key => $value) {
            if ($key === 'court') {
                if ($value === null) {
                    $contract->setCourt(null);
                } elseif (is_numeric($value)) {
                    $court = $this->courtRepository->find((int)$value);

                    if ($court !== null) {
                        $contract->setCourt($court);
                    }
                }

                continue;
            }

            if ($key === 'procedureInitiationMchs') {
                if ($value === null) {
                    $contract->setProcedureInitiationMchs(null);
                } elseif (is_numeric($value)) {
                    $mchs = $this->mchsRepository->find((int)$value);

                    if ($mchs !== null) {
                        $contract->setProcedureInitiationMchs($mchs);
                    }
                }

                continue;
            }

            if ($key === 'procedureInitiationGostekhnadzor') {
                if ($value === null) {
                    $contract->setProcedureInitiationGostekhnadzor(null);
                } elseif (is_numeric($value)) {
                    $gostekhnadzor = $this->gostekhnadzorRepository->find((int)$value);

                    if ($gostekhnadzor !== null) {
                        $contract->setProcedureInitiationGostekhnadzor($gostekhnadzor);
                    }
                }

                continue;
            }

            if ($key === 'procedureInitiationFns') {
                if ($value === null) {
                    $contract->setProcedureInitiationFns(null);
                } elseif (is_numeric($value)) {
                    $fns = $this->fnsRepository->find((int)$value);

                    if ($fns !== null) {
                        $contract->setProcedureInitiationFns($fns);
                    }
                }

                continue;
            }

            if ($key === 'procedureInitiationBailiff') {
                if ($value === null) {
                    $contract->setProcedureInitiationBailiff(null);
                } elseif (is_numeric($value)) {
                    $bailiff = $this->bailiffRepository->find((int)$value);

                    if ($bailiff !== null) {
                        $contract->setProcedureInitiationBailiff($bailiff);
                    }
                }

                continue;
            }

            if ($key === 'procedureInitiationRosgvardia') {
                if ($value === null) {
                    $contract->setProcedureInitiationRosgvardia(null);
                } elseif (is_numeric($value)) {
                    $rosgvardia = $this->rosgvardiaRepository->find((int)$value);

                    if ($rosgvardia !== null) {
                        $contract->setProcedureInitiationRosgvardia(procedureInitiationRosgvardia: $rosgvardia);
                    }
                }

                continue;
            }

            if ($key === 'preCourtCreditors') {
                $contract->getPreCourtCreditors()->clear();

                if (is_array($value)) {
                    $processedCreditorIds = [];

                    foreach ($value as $creditorData) {
                        if (!is_array($creditorData)) {
                            continue;
                        }

                        $creditorId = $creditorData['creditorId'] ?? null;
                        $id = $creditorData['id'] ?? null;

                        if (!is_numeric($creditorId) || (int)$creditorId === 0) {
                            continue;
                        }

                        $creditorIdInt = (int)$creditorId;

                        if (isset($processedCreditorIds[$creditorIdInt])) {
                            continue;
                        }

                        $creditor = $this->creditorRepository->find($creditorIdInt);

                        if ($creditor === null) {
                            continue;
                        }

                        $contractPreCourtCreditor = null;

                        if (is_numeric($id)) {
                            $contractPreCourtCreditor = $this->contractsPreCourtCreditorRepository->find((int)$id);

                            if ($contractPreCourtCreditor !== null && $contractPreCourtCreditor->getContract()->getId() !== $contract->getId()) {
                                $contractPreCourtCreditor = null;
                            }
                        }

                        if (!$contractPreCourtCreditor) {
                            $existingCreditor = $this->contractsPreCourtCreditorRepository->findOneBy(
                                [
                                    'contract' => $contract,
                                    'creditor' => $creditor,
                                ]
                            );

                            if ($existingCreditor !== null) {
                                $contractPreCourtCreditor = $existingCreditor;
                            } else {
                                $contractPreCourtCreditor = new ContractsPreCourtCreditor();
                                $contractPreCourtCreditor->setContract(contract: $contract);

                                $this->entityManager->persist($contractPreCourtCreditor);
                            }
                        }

                        $contractPreCourtCreditor->setCreditor(creditor: $creditor);
                        $processedCreditorIds[$creditorIdInt] = true;

                        if (isset($creditorData['creditContractNumber'])) {
                            $contractPreCourtCreditor->setCreditContractNumber($creditorData['creditContractNumber'] === '' ? null : $creditorData['creditContractNumber']);
                        }

                        if (isset($creditorData['creditContractDate'])) {
                            $date = $creditorData['creditContractDate'];
                            if ($date === '') {
                                $contractPreCourtCreditor->setCreditContractDate(null);
                            } else {
                                try {
                                    $dateObj = new \DateTime($date);
                                    $contractPreCourtCreditor->setCreditContractDate($dateObj);
                                } catch (\Exception $e) {
                                    $contractPreCourtCreditor->setCreditContractDate(null);
                                }
                            }
                        }

                        if (isset($creditorData['debtAmount'])) {
                            $contractPreCourtCreditor->setDebtAmount($creditorData['debtAmount'] === '' ? null : $creditorData['debtAmount']);
                        }

                        if (isset($creditorData['principalAmount'])) {
                            $contractPreCourtCreditor->setPrincipalAmount($creditorData['principalAmount'] === '' ? null : $creditorData['principalAmount']);
                        }

                        if (isset($creditorData['financialSanctions'])) {
                            $contractPreCourtCreditor->setFinancialSanctions($creditorData['financialSanctions'] === '' ? null : $creditorData['financialSanctions']);
                        }

                        $contract->addPreCourtCreditor($contractPreCourtCreditor);
                    }
                }

                continue;
            }

            if ($key === 'procedureInitiationIPEndings') {
                $ipEndings = $value;

                if (is_array($ipEndings) && empty($ipEndings)) {
                    $ipEndings = null;
                }
                $contract->setProcedureInitiationIPEndings($ipEndings);

                continue;
            }

            if ($key === 'creditorsClaims') {
                $contract->getCreditorsClaims()->clear();

                if (is_array($value)) {
                    $processedCreditorIds = [];

                    foreach ($value as $claimData) {
                        if (!is_array($claimData)) {
                            continue;
                        }

                        $creditorId = $claimData['creditorId'] ?? null;
                        $id = $claimData['id'] ?? null;

                        if (!is_numeric($creditorId) || (int)$creditorId === 0) {
                            continue;
                        }

                        $creditorIdInt = (int)$creditorId;

                        if (isset($processedCreditorIds[$creditorIdInt])) {
                            continue;
                        }

                        $creditor = $this->creditorRepository->find($creditorIdInt);

                        if ($creditor === null) {
                            continue;
                        }

                        $contractCreditorClaim = null;

                        if (is_numeric($id)) {
                            $contractCreditorClaim = $this->contractsCreditorsClaimRepository->find((int)$id);

                            if ($contractCreditorClaim !== null && $contractCreditorClaim->getContract()->getId() !== $contract->getId()) {
                                $contractCreditorClaim = null;
                            }
                        }

                        if (!$contractCreditorClaim) {
                            $existingClaim = $this->contractsCreditorsClaimRepository->findOneBy(
                                [
                                    'contract' => $contract,
                                    'creditor' => $creditor,
                                ]
                            );

                            if ($existingClaim !== null) {
                                $contractCreditorClaim = $existingClaim;
                            } else {
                                $contractCreditorClaim = new ContractsCreditorsClaim();
                                $contractCreditorClaim->setContract(contract: $contract);

                                $this->entityManager->persist($contractCreditorClaim);
                            }
                        }

                        $contractCreditorClaim->setCreditor(creditor: $creditor);
                        $processedCreditorIds[$creditorIdInt] = true;

                        if (isset($claimData['debtAmount'])) {
                            $contractCreditorClaim->setDebtAmount($claimData['debtAmount'] === '' ? null : $claimData['debtAmount']);
                        }

                        if (isset($claimData['principalAmount'])) {
                            $contractCreditorClaim->setPrincipalAmount($claimData['principalAmount'] === '' ? null : $claimData['principalAmount']);
                        }

                        if (isset($claimData['interest'])) {
                            $contractCreditorClaim->setInterest($claimData['interest'] === '' ? null : $claimData['interest']);
                        }

                        if (isset($claimData['penalty'])) {
                            $contractCreditorClaim->setPenalty($claimData['penalty'] === '' ? null : $claimData['penalty']);
                        }

                        if (isset($claimData['lateFee'])) {
                            $contractCreditorClaim->setLateFee($claimData['lateFee'] === '' ? null : $claimData['lateFee']);
                        }

                        if (isset($claimData['forfeiture'])) {
                            $contractCreditorClaim->setForfeiture($claimData['forfeiture'] === '' ? null : $claimData['forfeiture']);
                        }

                        if (isset($claimData['stateDuty'])) {
                            $contractCreditorClaim->setStateDuty($claimData['stateDuty'] === '' ? null : $claimData['stateDuty']);
                        }

                        if (isset($claimData['stateDutyForConsideration'])) {
                            $contractCreditorClaim->setStateDutyForConsideration($claimData['stateDutyForConsideration'] === '' ? null : $claimData['stateDutyForConsideration']);
                        }

                        if (isset($claimData['basis'])) {
                            $basis = $claimData['basis'];
                            if (is_array($basis) && empty($basis)) {
                                $basis = null;
                            }
                            $contractCreditorClaim->setBasis($basis);
                        }

                        if (isset($claimData['inclusion'])) {
                            $contractCreditorClaim->setInclusion($claimData['inclusion'] === '' ? null : (bool)$claimData['inclusion']);
                        }

                        if (isset($claimData['isCreditCard'])) {
                            $contractCreditorClaim->setIsCreditCard($claimData['isCreditCard'] === '' ? null : (bool)$claimData['isCreditCard']);
                        }

                        if (isset($claimData['creditCardDate'])) {
                            $creditCardDate = $claimData['creditCardDate'];
                            if ($creditCardDate === '') {
                                $contractCreditorClaim->setCreditCardDate(null);
                            } else {
                                $contractCreditorClaim->setCreditCardDate(new \DateTime($creditCardDate));
                            }
                        }

                        if (isset($claimData['judicialActDate'])) {
                            $judicialActDate = $claimData['judicialActDate'];
                            if ($judicialActDate === '') {
                                $contractCreditorClaim->setJudicialActDate(null);
                            } else {
                                $contractCreditorClaim->setJudicialActDate(new \DateTime($judicialActDate));
                            }
                        }

                        $this->updateClaimRegistryFields(claim: $contractCreditorClaim, claimData: $claimData);

                        $contract->addCreditorsClaim($contractCreditorClaim);
                    }
                }

                continue;
            }

            if ($key === self::PROPERTY_KEY) {
                $this->updateProperty(contract: $contract, items: is_array($value) ? $value : []);

                continue;
            }

            if ($key === 'manager') {
                if (empty($value)) {
                    $contract->setFinancialManager(null);
                } else {
                    $manager = $this->financialManagerRepository->find((int)$value);

                    if ($manager !== null) {
                        $contract->setFinancialManager($manager);
                    }
                }

                continue;
            }

            $setterName = 'set' . ucfirst($key);

            if (!$reflection->hasMethod($setterName)) {
                continue;
            }

            $method = $reflection->getMethod($setterName);

            if (!$method->isPublic()) {
                continue;
            }

            // Обработка полей типа date
            if (in_array($key, $dateFields, true) && is_string($value)) {
                if ($value === '') {
                    $method->invoke($contract, null);
                } else {
                    $method->invoke($contract, new \DateTime($value));
                }

                continue;
            }

            // Обработка полей типа datetime
            if (in_array($key, $dateTimeFields, true) && is_string($value)) {
                if ($value === '') {
                    $method->invoke($contract, null);
                } else {
                    $method->invoke($contract, new \DateTime($value));
                }

                continue;
            }

            // Обработка статуса
            if ($key === 'status' && $value !== null) {
                $method->invoke($contract, ContractStatus::from($value));

                continue;
            }

            // Обработка пустых строк для строковых полей - преобразуем в null
            if ($value === '') {
                $value = null;
            }

            $method->invoke($contract, $value);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateProcedureExtension(Contracts $contract, array $data): void
    {
        $status = array_key_exists(self::PROCEDURE_EXTENSION_STATUS_KEY, $data)
            ? ProcedureExtensionStatus::tryFrom((string)$data[self::PROCEDURE_EXTENSION_STATUS_KEY])
            : $contract->getProcedureExtensionStatus();

        $dates = array_key_exists(self::PROCEDURE_EXTENSION_DATES_KEY, $data)
            ? $this->filterIsoDates(value: $data[self::PROCEDURE_EXTENSION_DATES_KEY])
            : $contract->getProcedureExtensionDates() ?? [];

        $contract->changeProcedureExtension(status: $status, dates: $dates);
    }

    /**
     * @param array<int|string, mixed> $items
     */
    private function updateProperty(Contracts $contract, array $items): void
    {
        $keptIds = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $subtype = PropertySubtype::tryFrom((string)($item[self::PROPERTY_SUBTYPE_KEY] ?? ''));
            $name = $this->toNullableTrimmedString(value: $item[self::PROPERTY_NAME_KEY] ?? null);

            if ($subtype === null || $name === null) {
                continue;
            }

            $property = $this->findProperty(contract: $contract, id: $item['id'] ?? null);

            if ($property === null) {
                $property = new ContractsProperty();
                $property->setContract(contract: $contract);
                $this->entityManager->persist($property);
                $contract->addProperty($property);
            }

            $property
                ->setSubtype($subtype)
                ->setName($name)
                ->setOwnershipType($this->toNullableTrimmedString(value: $item['ownershipType'] ?? null))
                ->setLocation($this->toNullableTrimmedString(value: $item['location'] ?? null))
                ->setArea($this->toNullableTrimmedString(value: $item['area'] ?? null))
                ->setIdentificationNumber($this->toNullableTrimmedString(value: $item['identificationNumber'] ?? null))
                ->setPledgeInfo($this->toNullableTrimmedString(value: $item['pledgeInfo'] ?? null))
                ->setManagerValuation(MoneyHelperService::normalize(amount: $item['managerValuation'] ?? null))
                ->setAppraiserValuation(MoneyHelperService::normalize(amount: $item['appraiserValuation'] ?? null))
                ->setIsExcludedFromEstate(isset($item['isExcludedFromEstate']) ? (bool)$item['isExcludedFromEstate'] : null)
                ->setExclusionReason($this->toNullableTrimmedString(value: $item['exclusionReason'] ?? null))
                ->setExcludedValuation(MoneyHelperService::normalize(amount: $item['excludedValuation'] ?? null));

            $propertyId = $property->getId();

            if ($propertyId !== null) {
                $keptIds[$propertyId] = true;
            }
        }

        foreach ($contract->getProperty() as $property) {
            $propertyId = $property->getId();

            if ($propertyId !== null && !isset($keptIds[$propertyId])) {
                $contract->removeProperty($property);
                $this->entityManager->remove($property);
            }
        }
    }

    private function findProperty(Contracts $contract, mixed $id): ?ContractsProperty
    {
        if (!is_numeric($id)) {
            return null;
        }

        $property = $this->contractsPropertyRepository->find((int)$id);

        if ($property === null || $property->getContract()->getId() !== $contract->getId()) {
            return null;
        }

        return $property;
    }

    private function updateClaimRegistryFields(ContractsCreditorsClaim $claim, array $claimData): void
    {
        if (array_key_exists(self::CLAIM_REGISTRY_ENTRY_DATE_KEY, $claimData)) {
            $this->applyClaimDate(
                value: $claimData[self::CLAIM_REGISTRY_ENTRY_DATE_KEY],
                apply: static fn (?\DateTimeInterface $date): ContractsCreditorsClaim => $claim->setRegistryEntryDate($date),
            );
        }

        if (array_key_exists(self::CLAIM_ORIGIN_DATE_KEY, $claimData)) {
            $this->applyClaimDate(
                value: $claimData[self::CLAIM_ORIGIN_DATE_KEY],
                apply: static fn (?\DateTimeInterface $date): ContractsCreditorsClaim => $claim->setOriginDate($date),
            );
        }

        if (array_key_exists(self::CLAIM_OBLIGATION_TYPE_KEY, $claimData)) {
            $claim->setObligationType($this->toNullableTrimmedString(value: $claimData[self::CLAIM_OBLIGATION_TYPE_KEY]));
        }

        if (array_key_exists(self::CLAIM_DISPUTE_NUMBER_KEY, $claimData)) {
            $claim->setDisputeNumber($this->toNullableTrimmedString(value: $claimData[self::CLAIM_DISPUTE_NUMBER_KEY]));
        }

        if (array_key_exists(self::CLAIM_REPAID_AMOUNT_KEY, $claimData)) {
            $this->applyClaimRepaidAmount(claim: $claim, value: $claimData[self::CLAIM_REPAID_AMOUNT_KEY]);
        }
    }

    private function applyClaimRepaidAmount(ContractsCreditorsClaim $claim, mixed $value): void
    {
        $amount = $this->toNullableTrimmedString(value: $value);

        if ($amount === null) {
            $claim->setRepaidAmount(null);

            return;
        }

        $normalized = MoneyHelperService::normalize(amount: $amount);

        if ($normalized !== null) {
            $claim->setRepaidAmount($normalized);
        }
    }

    /**
     * @param callable(?\DateTimeInterface): mixed $apply
     */
    private function applyClaimDate(mixed $value, callable $apply): void
    {
        if ($value === null || $value === '') {
            $apply(null);

            return;
        }

        if (!is_string($value)) {
            return;
        }

        $parsed = \DateTime::createFromFormat('!' . self::ISO_DATE_FORMAT, $value);

        if ($parsed === false || $parsed->format(self::ISO_DATE_FORMAT) !== $value) {
            return;
        }

        $apply($parsed);
    }

    private function toNullableTrimmedString(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $trimmed = trim((string)$value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @return array<int, string>
     */
    private function filterIsoDates(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            $value,
            static function (mixed $date): bool {
                if (!is_string($date)) {
                    return false;
                }

                $parsed = \DateTime::createFromFormat('!' . self::ISO_DATE_FORMAT, $date);

                return $parsed !== false && $parsed->format(self::ISO_DATE_FORMAT) === $date;
            }
        ));
    }
}
