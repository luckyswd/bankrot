<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251119114803 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Populate rosgvardia table with initial data';
    }

    public function up(Schema $schema): void
    {
        $rosgvardiaData = [
            [
                'name' => 'Центр лицензионно-разрешительной работы Главного управления Росгвардии по г.Санкт-Петербургу и Ленинградской области',
                'address' => '190098, г. Санкт-Петербург, ул. Галерная, д. 27',
            ],
            [
                'name' => 'Центр лицензионно-разрешительной работы Главного управления Росгвардии по Республике Татарстан',
                'address' => '420030, г. Казань, ул. Лазарева, д. 16',
            ],
            [
                'name' => 'Центр лицензионно-разрешительной работы Главного управления Росгвардии по Оренбургской области',
                'address' => '460000, г. Оренбург, ул. Кобозева д. 58',
            ],
        ];

        $connection = $this->connection;

        foreach ($rosgvardiaData as $data) {
            $name = $connection->quote($data['name']);
            $address = $data['address'] !== null ? $connection->quote($data['address']) : 'NULL';

            $this->addSql(
                "INSERT INTO rosgvardia (name, address, created_at, updated_at) VALUES ({$name}, {$address}, NOW(), NOW())"
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM rosgvardia');
    }
}
