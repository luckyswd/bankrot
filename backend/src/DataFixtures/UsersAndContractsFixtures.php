<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Contracts;
use App\Entity\Enum\ContractStatus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class UsersAndContractsFixtures extends Fixture implements FixtureGroupInterface
{
    private const string DEFAULT_PASSWORD = '$2y$12$qgA2wXqpcKOyk7qErTnDWuAPtmu.u2/kXWWWgwdW4XYZYNFKiPH2K';

    private const array FIRST_NAMES = [
        'Иван', 'Петр', 'Сергей', 'Александр', 'Дмитрий', 'Андрей', 'Алексей', 'Максим',
        'Владимир', 'Николай', 'Михаил', 'Павел', 'Роман', 'Олег', 'Артем', 'Игорь',
        'Евгений', 'Денис', 'Антон', 'Константин', 'Виктор', 'Юрий', 'Василий', 'Станислав',
    ];

    private const array LAST_NAMES = [
        'Иванов', 'Петров', 'Сидоров', 'Смирнов', 'Кузнецов', 'Попов', 'Соколов', 'Лебедев',
        'Козлов', 'Новиков', 'Морозов', 'Петров', 'Волков', 'Соловьев', 'Васильев', 'Зайцев',
        'Павлов', 'Семенов', 'Голубев', 'Виноградов', 'Богданов', 'Воробьев', 'Федоров', 'Михайлов',
    ];

    private const array MIDDLE_NAMES = [
        'Иванович', 'Петрович', 'Сергеевич', 'Александрович', 'Дмитриевич', 'Андреевич',
        'Алексеевич', 'Максимович', 'Владимирович', 'Николаевич', 'Михайлович', 'Павлович',
        'Романович', 'Олегович', 'Артемович', 'Игоревич', 'Евгеньевич', 'Денисович',
    ];

    private const array CITIES = [
        'Москва', 'Санкт-Петербург', 'Новосибирск', 'Екатеринбург', 'Казань', 'Нижний Новгород',
        'Челябинск', 'Самара', 'Омск', 'Ростов-на-Дону', 'Уфа', 'Красноярск', 'Воронеж',
        'Пермь', 'Волгоград', 'Краснодар', 'Саратов', 'Тюмень', 'Тольятти', 'Ижевск',
    ];

    private const array STREETS = [
        'Ленина', 'Советская', 'Центральная', 'Мира', 'Победы', 'Комсомольская', 'Молодежная',
        'Садовая', 'Набережная', 'Первомайская', 'Октябрьская', 'Кирова', 'Гагарина',
        'Пушкина', 'Чехова', 'Горького', 'Толстого', 'Достоевского', 'Некрасова', 'Тургенева',
    ];

    private const array EMPLOYER_NAMES = [
        'ООО "Рога и Копыта"', 'ООО "СтройМатериалы"', 'ООО "Торговый Дом"', 'ООО "ТехСервис"',
        'ООО "ПромСнаб"', 'ООО "СтройГрупп"', 'ООО "ТрансЛогистик"', 'ООО "МеталлСервис"',
        'ООО "ЭнергоСтрой"', 'ООО "АвтоТранс"', 'ООО "СтройИнвест"', 'ООО "ПромТех"',
        'ООО "СтройКомплекс"', 'ООО "ТоргСервис"', 'ООО "СтройПроект"', 'ООО "ПромСтрой"',
    ];

    public function load(ObjectManager $manager): void
    {
        $users = $this->createUsers($manager);
        $this->createContracts($manager, $users);

        $manager->flush();
    }

    /**
     * @return array<string>
     */
    public static function getGroups(): array
    {
        return ['seed'];
    }

    /**
     * @return array<int, User>
     */
    private function createUsers(ObjectManager $manager): array
    {
        $users = [];

        $admin = new User();
        $admin->setUsername('admin');
        $admin->setPassword(self::DEFAULT_PASSWORD);
        $admin->setFio('Администратор');
        $admin->setRoles(['ROLE_ADMIN']);

        $manager->persist($admin);
        $users[] = $admin;
        $this->addReference('admin', $admin);

        for ($i = 1; $i <= 20; ++$i) {
            $user = new User();
            $user->setUsername('user' . str_pad((string)$i, 3, '0', STR_PAD_LEFT));
            $user->setPassword(self::DEFAULT_PASSWORD);
            $user->setFio($this->generateFio());
            $user->setRoles(['ROLE_USER']);

            $manager->persist($user);
            $users[] = $user;
            $this->addReference('user_' . $i, $user);
        }

        return $users;
    }

    /**
     * @param array<int, User> $users
     */
    private function createContracts(ObjectManager $manager, array $users): void
    {
        $usersCount = count($users);
        $contractManagers = array_slice($users, 0, min(5, $usersCount));

        for ($i = 1; $i <= 200; ++$i) {
            $contract = new Contracts();
            $author = $users[array_rand($users)];
            $contractManager = $contractManagers[array_rand($contractManagers)];

            $contract->setAuthor($author);
            $contract->setManager($contractManager);
            $contract->setStatus($i % 3 === 0 ? ContractStatus::COMPLETED : ContractStatus::IN_PROGRESS);
            $contract->setContractNumber('ДГ-' . date('Y') . '-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT));
            $contract->setContractDate($this->generateRandomDate());

            $this->fillContractPersonalData($contract);
            $this->fillContractAddressData($contract);
            $this->fillContractPassportData($contract);
            $this->fillContractAdditionalData($contract);

            $manager->persist($contract);
            $this->addReference('contract_' . $i, $contract);
        }
    }

    private function fillContractPersonalData(Contracts $contract): void
    {
        $firstName = self::FIRST_NAMES[array_rand(self::FIRST_NAMES)];
        $lastName = self::LAST_NAMES[array_rand(self::LAST_NAMES)];
        $middleName = self::MIDDLE_NAMES[array_rand(self::MIDDLE_NAMES)];

        $contract->setFirstName($firstName);
        $contract->setLastName($lastName);
        $contract->setMiddleName($middleName);

        $contract->setBirthDate($this->generateRandomDate(startYear: 1950, endYear: 2000));
        $contract->setBirthPlace('г. ' . self::CITIES[array_rand(self::CITIES)]);
        $contract->setSnils($this->generateSnils());

        if (rand(0, 10) > 7) {
            $contract->setIsLastNameChanged(true);
            $contract->setChangedLastName($this->generateFio());
        }
    }

    private function fillContractAddressData(Contracts $contract): void
    {
        $city = self::CITIES[array_rand(self::CITIES)];
        $street = self::STREETS[array_rand(self::STREETS)];

        $contract->setRegistrationRegion($city);
        $contract->setRegistrationCity($city);
        $contract->setRegistrationStreet($street);
        $contract->setRegistrationHouse((string)rand(1, 200));
        $contract->setRegistrationApartment((string)rand(1, 300));

        if (rand(0, 10) > 6) {
            $contract->setRegistrationBuilding((string)rand(1, 5));
        }

        if (rand(0, 10) > 8) {
            $contract->setRegistrationDistrict('Район ' . rand(1, 20));
        }
    }

    private function fillContractPassportData(Contracts $contract): void
    {
        $contract->setPassportSeries(str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT));
        $contract->setPassportNumber(str_pad((string)rand(100000, 999999), 6, '0', STR_PAD_LEFT));
        $contract->setPassportIssuedBy('ОУФМС России по ' . self::CITIES[array_rand(self::CITIES)]);
        $contract->setPassportIssuedDate($this->generateRandomDate(startYear: 2000, endYear: 2020));
        $contract->setPassportDepartmentCode(
            str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT) . '-' .
            str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT)
        );
    }

    private function fillContractAdditionalData(Contracts $contract): void
    {
        $contract->setPhone('+7 (' . rand(900, 999) . ') ' . rand(100, 999) . '-' . rand(10, 99) . '-' . rand(10, 99));
        $contract->setEmail('contract' . rand(1000, 9999) . '@example.com');

        if (rand(0, 10) > 3) {
            $maritalStatuses = ['Да', 'Нет', 'Не состоял в течение 3 лет'];
            $contract->setMaritalStatus($maritalStatuses[array_rand($maritalStatuses)]);

            if ($contract->getMaritalStatus() === 'Да') {
                $contract->setSpouseFullName($this->generateFio());
                $contract->setSpouseBirthDate($this->generateRandomDate(startYear: 1970, endYear: 2000));
            }
        }

        if (rand(0, 10) > 5) {
            $contract->setHasMinorChildren(rand(0, 1) === 1);

            if ($contract->hasMinorChildren()) {
                $childrenCount = rand(1, 3);
                $children = [];

                for ($i = 0; $i < $childrenCount; ++$i) {
                    $children[] = [
                        'firstName' => self::FIRST_NAMES[array_rand(self::FIRST_NAMES)],
                        'lastName' => $contract->getLastName(),
                        'middleName' => self::MIDDLE_NAMES[array_rand(self::MIDDLE_NAMES)],
                        'isLastNameChanged' => false,
                        'changedLastName' => null,
                        'birthDate' => $this->generateRandomDate(startYear: 2010, endYear: 2020)->format('Y-m-d'),
                    ];
                }

                $contract->setChildren($children);
            }
        }

        if (rand(0, 10) > 4) {
            $contract->setEmployerName(self::EMPLOYER_NAMES[array_rand(self::EMPLOYER_NAMES)]);
            $contract->setEmployerAddress('г. ' . self::CITIES[array_rand(self::CITIES)] . ', ул. ' . self::STREETS[array_rand(self::STREETS)] . ', д. ' . rand(1, 100));
            $contract->setEmployerInn(str_pad((string)rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT));
        }

        if (rand(0, 10) > 6) {
            $contract->setIsStudent(rand(0, 1) === 1);
        }

        if (rand(0, 10) > 5) {
            $benefits = ['Пособие по безработице', 'ЕДВ', 'Алименты', 'Пенсия по инвалидности'];
            $contract->setSocialBenefits($benefits[array_rand($benefits)]);
        }

        $contract->setDebtAmount((string)rand(100000, 5000000) . '.' . str_pad((string)rand(0, 99), 2, '0', STR_PAD_LEFT));
        $contract->setHasEnforcementProceedings(rand(0, 1) === 1);

        $contract->setMailingAddress(
            rand(100000, 999999) . ', ' .
            'г. ' . self::CITIES[array_rand(self::CITIES)] . ', ' .
            'ул. ' . self::STREETS[array_rand(self::STREETS)] . ', ' .
            'д. ' . rand(1, 200) . ', ' .
            'кв. ' . rand(1, 300)
        );
    }

    private function generateFio(): string
    {
        return self::LAST_NAMES[array_rand(self::LAST_NAMES)] . ' ' .
            self::FIRST_NAMES[array_rand(self::FIRST_NAMES)] . ' ' .
            self::MIDDLE_NAMES[array_rand(self::MIDDLE_NAMES)];
    }

    private function generateSnils(): string
    {
        $part1 = rand(100, 999);
        $part2 = rand(100, 999);
        $part3 = rand(100, 999);
        $part4 = rand(10, 99);

        return sprintf('%03d-%03d-%03d %02d', $part1, $part2, $part3, $part4);
    }

    private function generateRandomDate(int $startYear = 2020, int $endYear = 2024): \DateTime
    {
        $start = mktime(0, 0, 0, 1, 1, $startYear);
        $end = mktime(0, 0, 0, 12, 31, $endYear);
        $randomTimestamp = rand($start, $end);

        return new \DateTime('@' . $randomTimestamp);
    }
}
