<?php

declare(strict_types=1);

namespace App\DataFixtures\Test;

use App\Entity\Contracts;
use App\Entity\Enum\ContractStatus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestUserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user1 = new User();
        $user1->setUsername('test_user1');
        $user1->setPassword('$2y\$12\$qgA2wXqpcKOyk7qErTnDWuAPtmu.u2/kXWWWgwdW4XYZYNFKiPH2K');
        $user1->setFio('Test User 1');
        $manager->persist($user1);
        $this->addReference('user1', $user1);

        $user2 = new User();
        $user2->setUsername('test_user2');
        $user2->setPassword('$2y\$12\$qgA2wXqpcKOyk7qErTnDWuAPtmu.u2/kXWWWgwdW4XYZYNFKiPH2K');
        $user2->setFio('Test User 2');
        $manager->persist($user2);
        $this->addReference('user2', $user2);

        $manager1 = new User();
        $manager1->setUsername('test_manager');
        $manager1->setPassword('$2y\$12\$qgA2wXqpcKOyk7qErTnDWuAPtmu.u2/kXWWWgwdW4XYZYNFKiPH2K');
        $manager1->setFio('Test Manager');
        $manager->persist($manager1);
        $this->addReference('manager', $manager1);

        $contract1 = new Contracts();
        $contract1->setAuthor($user1);
        $contract1->setManager($manager1);
        $contract1->setStatus(ContractStatus::IN_PROGRESS);
        $contract1->setContractNumber('CONTRACT-001');
        $contract1->setContractDate(new \DateTime('2024-01-01'));
        $contract1->setFirstName('Иван');
        $contract1->setLastName('Иванов');
        $contract1->setMiddleName('Иванович');
        $manager->persist($contract1);
        $this->addReference('contract1', $contract1);

        $contract2 = new Contracts();
        $contract2->setAuthor($user2);
        $contract2->setManager($manager1);
        $contract2->setStatus(ContractStatus::COMPLETED);
        $contract2->setContractNumber('CONTRACT-002');
        $contract2->setContractDate(new \DateTime('2024-01-02'));
        $contract2->setFirstName('Петр');
        $contract2->setLastName('Петров');
        $contract2->setMiddleName('Петрович');
        $manager->persist($contract2);
        $this->addReference('contract2', $contract2);

        for ($i = 3; $i <= 25; ++$i) {
            $contract = new Contracts();
            $contract->setAuthor($user1);
            $contract->setManager($manager1);
            $contract->setStatus($i % 2 === 0 ? ContractStatus::COMPLETED : ContractStatus::IN_PROGRESS);
            $contract->setContractNumber('CONTRACT-' . str_pad((string)$i, 3, '0', STR_PAD_LEFT));
            $contract->setContractDate(new \DateTime('2024-01-' . str_pad((string)$i, 2, '0', STR_PAD_LEFT)));
            $contract->setFirstName('Имя' . $i);
            $contract->setLastName('Фамилия' . $i);
            $contract->setMiddleName('Отчество' . $i);
            $manager->persist($contract);
        }

        $manager->flush();
    }
}
