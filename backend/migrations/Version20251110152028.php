<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110152028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove phone field from courts table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE courts DROP phone');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE courts ADD phone VARCHAR(50) DEFAULT NULL');
    }
}
