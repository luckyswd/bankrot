<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915175017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Требования кредиторов: дата внесения в реестр, вид обязательства, номер обособленного спора, дата возникновения и сумма погашения';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contracts_creditors_claim ADD registry_entry_date DATE DEFAULT NULL, ADD obligation_type VARCHAR(255) DEFAULT NULL, ADD dispute_number VARCHAR(255) DEFAULT NULL, ADD origin_date DATE DEFAULT NULL, ADD repaid_amount NUMERIC(15, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contracts_creditors_claim DROP registry_entry_date, DROP obligation_type, DROP dispute_number, DROP origin_date, DROP repaid_amount');
    }
}
