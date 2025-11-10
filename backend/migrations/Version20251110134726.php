<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110134726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add code field to mchs table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mchs ADD code VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mchs DROP code');
    }
}
