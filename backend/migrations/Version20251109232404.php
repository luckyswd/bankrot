<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251109232404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create mchs and rosgvardia tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE mchs (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(500) NOT NULL,
            address LONGTEXT DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE rosgvardia (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(500) NOT NULL,
            address LONGTEXT DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE mchs');
        $this->addSql('DROP TABLE rosgvardia');
    }
}
