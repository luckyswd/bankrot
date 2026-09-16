<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916141757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Финансовый анализ: признак получателя социальных выплат и дата дополнения анализа';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contracts ADD is_social_payments_recipient TINYINT(1) DEFAULT NULL, ADD financial_analysis_supplement_date DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contracts DROP is_social_payments_recipient, DROP financial_analysis_supplement_date');
    }
}
