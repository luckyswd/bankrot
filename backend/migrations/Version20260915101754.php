<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915101754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Данные судебных стадий для отчёта финансового управляющего: публикации, уведомление кредиторов, продление процедуры, опись, ЗАГС, результаты рассмотрения требований, расходы, публикация о признаках банкротства';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contracts ADD procedure_initiation_efrsb_message_number VARCHAR(255) DEFAULT NULL, ADD procedure_initiation_efrsb_message_date DATE DEFAULT NULL, ADD procedure_initiation_kommersant_issue_number VARCHAR(255) DEFAULT NULL, ADD procedure_initiation_kommersant_ad_number VARCHAR(255) DEFAULT NULL, ADD procedure_initiation_kommersant_publication_date DATE DEFAULT NULL, ADD procedure_initiation_kommersant_publication_cost NUMERIC(15, 2) DEFAULT NULL, ADD procedure_initiation_creditors_notification_date DATE DEFAULT NULL, ADD procedure_extension_status VARCHAR(20) DEFAULT NULL, ADD procedure_extension_dates JSON DEFAULT NULL, ADD property_inventory_date DATE DEFAULT NULL, ADD zags_department VARCHAR(500) DEFAULT NULL, ADD zags_certificate_period_from DATE DEFAULT NULL, ADD zags_certificate_period_to DATE DEFAULT NULL, ADD claims_included_count INT DEFAULT NULL, ADD claims_rejected_count INT DEFAULT NULL, ADD efrsb_expenses_amount NUMERIC(15, 2) DEFAULT NULL, ADD efrsb_expenses_paid NUMERIC(15, 2) DEFAULT NULL, ADD postal_expenses_amount NUMERIC(15, 2) DEFAULT NULL, ADD postal_expenses_paid NUMERIC(15, 2) DEFAULT NULL, ADD newspaper_expenses_paid NUMERIC(15, 2) DEFAULT NULL, ADD future_efrsb_expenses_amount NUMERIC(15, 2) DEFAULT NULL, ADD future_efrsb_expenses_paid NUMERIC(15, 2) DEFAULT NULL, ADD bankruptcy_signs_efrsb_publication_date DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contracts DROP procedure_initiation_efrsb_message_number, DROP procedure_initiation_efrsb_message_date, DROP procedure_initiation_kommersant_issue_number, DROP procedure_initiation_kommersant_ad_number, DROP procedure_initiation_kommersant_publication_date, DROP procedure_initiation_kommersant_publication_cost, DROP procedure_initiation_creditors_notification_date, DROP procedure_extension_status, DROP procedure_extension_dates, DROP property_inventory_date, DROP zags_department, DROP zags_certificate_period_from, DROP zags_certificate_period_to, DROP claims_included_count, DROP claims_rejected_count, DROP efrsb_expenses_amount, DROP efrsb_expenses_paid, DROP postal_expenses_amount, DROP postal_expenses_paid, DROP newspaper_expenses_paid, DROP future_efrsb_expenses_amount, DROP future_efrsb_expenses_paid, DROP bankruptcy_signs_efrsb_publication_date');
    }
}
