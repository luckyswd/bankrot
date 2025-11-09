<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251109231243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create bailiffs table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE bailiffs (
            id INT AUTO_INCREMENT NOT NULL,
            department VARCHAR(500) NOT NULL,
            address LONGTEXT DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE bailiffs');
    }
}

