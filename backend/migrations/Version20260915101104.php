<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915101104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Договор страхования ответственности арбитражного управляющего в справочнике финансовых управляющих';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE financial_managers ADD insurance_contract_number VARCHAR(255) DEFAULT NULL, ADD insurance_contract_date DATE DEFAULT NULL, ADD insurance_start_date DATE DEFAULT NULL, ADD insurance_end_date DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE financial_managers DROP insurance_contract_number, DROP insurance_contract_date, DROP insurance_start_date, DROP insurance_end_date');
    }
}
