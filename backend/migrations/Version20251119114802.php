<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251119114802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update rosgvardia table: remove phone field';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rosgvardia DROP phone');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rosgvardia ADD phone VARCHAR(50) DEFAULT NULL');
    }
}
