<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Enum\ContractStatus;
use App\Repository\ContractsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:calculate-children-full-age',
    description: 'Вычисляет и сохраняет количество полных лет для всех детей во всех контрактах',
)]
class CalculateChildrenFullAgeCommand extends Command
{
    public function __construct(
        private readonly ContractsRepository $contractsRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $contracts = $this->contractsRepository->findBy(criteria: ['status' => ContractStatus::IN_PROGRESS]);

        foreach ($contracts as $contract) {
            $children = $contract->getChildren();

            if (!empty($children)) {
                $childrenUpdated = false;
                $updatedChildrenArray = [];

                foreach ($children as $child) {
                    if (empty($child['birthDate'])) {
                        $updatedChildrenArray[] = $child;

                        continue;
                    }

                    $fullAge = $this->calculateFullAge(birthDate: $child['birthDate']);

                    $child['fullAge'] = $fullAge;
                    $updatedChildrenArray[] = $child;

                    $childrenUpdated = true;
                }

                if ($childrenUpdated) {
                    $contract->setChildren(children: $updatedChildrenArray);
                    $this->entityManager->persist($contract);
                }
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }

    /**
     * Вычисляет количество полных лет на основе даты рождения.
     *
     * @param string $birthDate Дата рождения в формате Y-m-d
     *
     * @return int|null Количество полных лет или null, если дата невалидна или в будущем
     */
    private function calculateFullAge(string $birthDate): ?int
    {
        try {
            $birth = new \DateTime($birthDate);
            $today = new \DateTime();
            $today->setTime(hour: 0, minute: 0, second: 0);
            $birth->setTime(hour: 0, minute: 0, second: 0);

            // Если дата рождения в будущем, возвращаем null
            if ($birth > $today) {
                return null;
            }

            $age = $today->diff($birth);

            return $age->y;
        } catch (\Exception $e) {
            return null;
        }
    }
}
