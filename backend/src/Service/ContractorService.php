<?php

namespace App\Service;

use App\Entity\Contracts;
use App\Entity\ContractsCreditorsClaim;
use App\Entity\Enum\BankruptcyStage;
use App\Entity\Enum\ContractStatus;
use App\Repository\BailiffRepository;
use App\Repository\ContractsCreditorsClaimRepository;
use App\Repository\CourtRepository;
use App\Repository\CreditorRepository;
use App\Repository\DocumentTemplateRepository;
use App\Repository\FnsRepository;
use App\Repository\GostekhnadzorRepository;
use App\Repository\MchsRepository;
use App\Repository\RosgvardiaRepository;
use App\Repository\UserRepository;
use App\Service\Templates\DocumentTemplateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ContractorService
{
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
        private readonly UserRepository $userRepository,
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
            'procedureInitiationDecisionDate',
            'procedureInitiationResolutionDate',
            'procedureInitiationDocumentDate',
        ];
        $dateTimeFields = [
            'hearingDateTime',
            'efrsbDateTime',
            'marriageTerminationDate',
        ];

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

            if ($key === 'creditors') {
                $contract->getCreditors()->clear();

                if (is_array($value)) {
                    foreach ($value as $creditorId) {
                        if (is_numeric($creditorId)) {
                            $creditor = $this->creditorRepository->find((int)$creditorId);

                            if ($creditor) {
                                $contract->addCreditor($creditor);
                            }
                        }
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

                        $contract->addCreditorsClaim($contractCreditorClaim);
                    }
                }

                continue;
            }

            if ($key === 'manager') {
                if (empty($value)) {
                    $contract->setManager(null);
                } else {
                    $manager = $this->userRepository->find((int)$value);

                    if ($manager !== null) {
                        $contract->setManager($manager);
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
                $method->invoke($contract, new \DateTime($value));

                continue;
            }

            // Обработка полей типа datetime
            if (in_array($key, $dateTimeFields, true) && is_string($value)) {
                $method->invoke($contract, new \DateTime($value));

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
}
