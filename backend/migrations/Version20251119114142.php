<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251119114142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update bailiffs table: remove phone field and add head_full_name field';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bailiffs DROP phone');
        $this->addSql('ALTER TABLE bailiffs ADD head_full_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bailiffs DROP head_full_name');
        $this->addSql('ALTER TABLE bailiffs ADD phone VARCHAR(50) DEFAULT NULL');
    }
}
