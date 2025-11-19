<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Bailiff;
use App\Entity\Court;
use App\Entity\Creditor;
use App\Entity\Fns;
use App\Entity\Mchs;
use App\Entity\Rosgvardia;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class DirectoriesFixtures extends Fixture implements FixtureGroupInterface
{
    private const array CREDITOR_NAMES = [
        'ПАО Сбербанк',
        'ПАО ВТБ',
        'ПАО Газпромбанк',
        'ПАО Альфа-Банк',
        'ПАО Россельхозбанк',
        'ПАО Банк "Открытие"',
        'ПАО Райффайзенбанк',
        'ПАО ЮниКредит Банк',
        'ПАО Росбанк',
        'ПАО Тинькофф Банк',
        'ООО "Хоум Кредит энд Финанс Банк"',
        'ПАО Совкомбанк',
        'ПАО Почта Банк',
        'ПАО Банк Зенит',
        'ПАО МКБ',
        'ООО "Ренессанс Кредит"',
        'ПАО Банк Уралсиб',
        'ПАО Банк "Санкт-Петербург"',
        'ПАО Банк "Возрождение"',
        'ПАО АКБ "Абсолют Банк"',
    ];

    private const array COURT_NAMES = [
        'Арбитражный суд города Москвы',
        'Арбитражный суд Московской области',
        'Арбитражный суд города Санкт-Петербурга и Ленинградской области',
        'Арбитражный суд Новосибирской области',
        'Арбитражный суд Свердловской области',
        'Арбитражный суд Республики Татарстан',
        'Арбитражный суд Нижегородской области',
        'Арбитражный суд Челябинской области',
        'Арбитражный суд Самарской области',
        'Арбитражный суд Омской области',
        'Арбитражный суд Ростовской области',
        'Арбитражный суд Республики Башкортостан',
        'Арбитражный суд Красноярского края',
        'Арбитражный суд Воронежской области',
        'Арбитражный суд Пермского края',
    ];

    private const array BAILIFF_DEPARTMENTS = [
        'Отделение судебных приставов по Центральному району г. Москвы',
        'Отделение судебных приставов по Северному району г. Москвы',
        'Отделение судебных приставов по Южному району г. Москвы',
        'Отделение судебных приставов по Восточному району г. Москвы',
        'Отделение судебных приставов по Западному району г. Москвы',
        'Отделение судебных приставов по Центральному району г. Санкт-Петербурга',
        'Отделение судебных приставов по Московскому району г. Санкт-Петербурга',
        'Отделение судебных приставов по Невскому району г. Санкт-Петербурга',
        'Отделение судебных приставов по Новосибирскому району',
        'Отделение судебных приставов по Екатеринбургскому району',
        'Отделение судебных приставов по Казанскому району',
        'Отделение судебных приставов по Нижегородскому району',
        'Отделение судебных приставов по Челябинскому району',
        'Отделение судебных приставов по Самарскому району',
        'Отделение судебных приставов по Ростовскому району',
    ];

    private const array FNS_NAMES = [
        'Межрайонная ИФНС России № 1 по г. Москве',
        'Межрайонная ИФНС России № 2 по г. Москве',
        'Межрайонная ИФНС России № 3 по г. Москве',
        'Межрайонная ИФНС России № 4 по г. Москве',
        'Межрайонная ИФНС России № 5 по г. Москве',
        'Межрайонная ИФНС России № 6 по г. Москве',
        'Межрайонная ИФНС России № 7 по г. Москве',
        'Межрайонная ИФНС России № 8 по г. Москве',
        'Межрайонная ИФНС России № 9 по г. Москве',
        'Межрайонная ИФНС России № 10 по г. Москве',
        'Межрайонная ИФНС России № 1 по г. Санкт-Петербургу',
        'Межрайонная ИФНС России № 2 по г. Санкт-Петербургу',
        'Межрайонная ИФНС России № 3 по г. Санкт-Петербургу',
        'Межрайонная ИФНС России № 4 по г. Санкт-Петербургу',
        'Межрайонная ИФНС России № 5 по г. Санкт-Петербургу',
    ];

    private const array MCHS_NAMES = [
        'ГИМС МЧС России по г. Москве',
        'ГИМС МЧС России по Московской области',
        'ГИМС МЧС России по г. Санкт-Петербургу',
        'ГИМС МЧС России по Ленинградской области',
        'ГИМС МЧС России по Новосибирской области',
        'ГИМС МЧС России по Свердловской области',
        'ГИМС МЧС России по Республике Татарстан',
        'ГИМС МЧС России по Нижегородской области',
        'ГИМС МЧС России по Челябинской области',
        'ГИМС МЧС России по Самарской области',
        'ГИМС МЧС России по Омской области',
        'ГИМС МЧС России по Ростовской области',
        'ГИМС МЧС России по Республике Башкортостан',
        'ГИМС МЧС России по Красноярскому краю',
        'ГИМС МЧС России по Воронежской области',
    ];

    private const array ROSGVARDIA_NAMES = [
        'Управление Росгвардии по г. Москве',
        'Управление Росгвардии по Московской области',
        'Управление Росгвардии по г. Санкт-Петербургу',
        'Управление Росгвардии по Ленинградской области',
        'Управление Росгвардии по Новосибирской области',
        'Управление Росгвардии по Свердловской области',
        'Управление Росгвардии по Республике Татарстан',
        'Управление Росгвардии по Нижегородской области',
        'Управление Росгвардии по Челябинской области',
        'Управление Росгвардии по Самарской области',
        'Управление Росгвардии по Омской области',
        'Управление Росгвардии по Ростовской области',
        'Управление Росгвардии по Республике Башкортостан',
        'Управление Росгвардии по Красноярскому краю',
        'Управление Росгвардии по Воронежской области',
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

    private const array HEAD_NAMES = [
        'ИВАНОВ ИВАН ИВАНОВИЧ',
        'ПЕТРОВ ПЕТР ПЕТРОВИЧ',
        'СИДОРОВ СИДОР СИДОРОВИЧ',
        'КОЗЛОВ АЛЕКСАНДР АЛЕКСАНДРОВИЧ',
        'СМИРНОВ ДМИТРИЙ ВЛАДИМИРОВИЧ',
        'ПОПОВ СЕРГЕЙ НИКОЛАЕВИЧ',
        'НОВИКОВ АНДРЕЙ БОРИСОВИЧ',
        'МОРОЗОВ ВЛАДИМИР СЕРГЕЕВИЧ',
        'ВОЛКОВ АЛЕКСЕЙ ИГОРЕВИЧ',
        'СОКОЛОВ МАКСИМ ВИКТОРОВИЧ',
    ];

    public function load(ObjectManager $manager): void
    {
        $this->createCreditors($manager);
        $this->createCourts($manager);
        $this->createBailiffs($manager);
        $this->createFns($manager);
        $this->createMchs($manager);
        $this->createRosgvardia($manager);

        $manager->flush();
    }

    /**
     * @return array<string>
     */
    public static function getGroups(): array
    {
        return ['seed'];
    }

    private function createCreditors(ObjectManager $manager): void
    {
        foreach (self::CREDITOR_NAMES as $index => $name) {
            $creditor = new Creditor();
            $creditor->setName($name);
            $creditor->setAddress($this->generateAddress());
            $creditor->setHeadFullName(self::HEAD_NAMES[array_rand(self::HEAD_NAMES)]);
            $creditor->setBankDetails($this->generateBankDetails());

            $manager->persist($creditor);
            $this->addReference('creditor_' . ($index + 1), $creditor);
        }
    }

    private function createCourts(ObjectManager $manager): void
    {
        foreach (self::COURT_NAMES as $index => $name) {
            $court = new Court();
            $court->setName($name);
            $court->setAddress($this->generateAddress());

            $manager->persist($court);
            $this->addReference('court_' . ($index + 1), $court);
        }
    }

    private function createBailiffs(ObjectManager $manager): void
    {
        foreach (self::BAILIFF_DEPARTMENTS as $index => $department) {
            $bailiff = new Bailiff();
            $bailiff->setDepartment($department);
            $bailiff->setAddress($this->generateAddress());
            $bailiff->setHeadFullName(self::HEAD_NAMES[array_rand(self::HEAD_NAMES)]);

            $manager->persist($bailiff);
            $this->addReference('bailiff_' . ($index + 1), $bailiff);
        }
    }

    private function createFns(ObjectManager $manager): void
    {
        foreach (self::FNS_NAMES as $index => $name) {
            $fns = new Fns();
            $fns->setName($name);
            $fns->setAddress($this->generateAddress());
            $fns->setCode($this->generateFnsCode());

            $manager->persist($fns);
            $this->addReference('fns_' . ($index + 1), $fns);
        }
    }

    private function createMchs(ObjectManager $manager): void
    {
        foreach (self::MCHS_NAMES as $index => $name) {
            $mchs = new Mchs();
            $mchs->setName($name);
            $mchs->setAddress($this->generateAddress());
            $mchs->setPhone($this->generatePhone());
            $mchs->setCode($this->generateFnsCode());

            $manager->persist($mchs);
            $this->addReference('mchs_' . ($index + 1), $mchs);
        }
    }

    private function createRosgvardia(ObjectManager $manager): void
    {
        foreach (self::ROSGVARDIA_NAMES as $index => $name) {
            $rosgvardia = new Rosgvardia();
            $rosgvardia->setName($name);
            $rosgvardia->setAddress($this->generateAddress());

            $manager->persist($rosgvardia);
            $this->addReference('rosgvardia_' . ($index + 1), $rosgvardia);
        }
    }

    private function generateBankDetails(): string
    {
        $bik = str_pad((string)rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
        $inn = str_pad((string)rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);
        $kpp = str_pad((string)rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
        $corrAccount = '30101810' . str_pad((string)rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
        $settlementAccount = '40702810' . str_pad((string)rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);

        return "БИК: {$bik}, ИНН: {$inn}, КПП: {$kpp}, КОРР.СЧЕТ: {$corrAccount}, РАСЧЕТНЫЙ СЧЕТ: {$settlementAccount}";
    }

    private function generateFnsCode(): string
    {
        return str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function generateAddress(): string
    {
        $city = self::CITIES[array_rand(self::CITIES)];
        $street = self::STREETS[array_rand(self::STREETS)];
        $house = rand(1, 200);
        $building = rand(0, 10) > 7 ? ', корп. ' . rand(1, 5) : '';
        $apartment = rand(0, 10) > 5 ? ', кв. ' . rand(1, 300) : '';

        return 'г. ' . $city . ', ул. ' . $street . ', д. ' . $house . $building . $apartment;
    }

    private function generatePhone(): string
    {
        return '+7 (' . rand(495, 999) . ') ' . rand(100, 999) . '-' . rand(10, 99) . '-' . rand(10, 99);
    }
}
