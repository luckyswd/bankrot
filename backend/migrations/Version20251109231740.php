<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251109231740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create fns table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE fns (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(500) NOT NULL,
            address LONGTEXT DEFAULT NULL,
            code VARCHAR(50) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE fns');
    }
}
