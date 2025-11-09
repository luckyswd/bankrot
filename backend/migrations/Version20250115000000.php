<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250115000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create creditors table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE creditors (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(500) NOT NULL,
            inn VARCHAR(12) DEFAULT NULL,
            ogrn VARCHAR(15) DEFAULT NULL,
            type VARCHAR(50) DEFAULT NULL,
            address LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE creditors');
    }
}

