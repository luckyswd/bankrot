<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916135821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Даты движения дела: подача заявления в арбитражный суд и возбуждение дела о банкротстве';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contracts ADD court_application_submission_date DATE DEFAULT NULL, ADD case_initiation_date DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contracts DROP court_application_submission_date, DROP case_initiation_date');
    }
}
