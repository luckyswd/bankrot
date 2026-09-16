<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916184538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Имущество должника: поля категорий формы описи';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contracts_property ADD account_type VARCHAR(255) DEFAULT NULL, ADD opened_at DATE DEFAULT NULL, ADD amount NUMERIC(15, 2) DEFAULT NULL, ADD currency VARCHAR(50) DEFAULT NULL, ADD issuer VARCHAR(255) DEFAULT NULL, ADD participation_share VARCHAR(100) DEFAULT NULL, ADD quantity VARCHAR(50) DEFAULT NULL, ADD obligation_content VARCHAR(500) DEFAULT NULL, ADD basis_text VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contracts_property DROP account_type, DROP opened_at, DROP amount, DROP currency, DROP issuer, DROP participation_share, DROP quantity, DROP obligation_content, DROP basis_text');
    }
}
