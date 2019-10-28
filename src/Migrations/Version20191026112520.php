<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\DBALException;

/**
* Class Version20191026112520
*/
final class Version20191026112520 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @param Schema $schema
     *
     * @return void
     * @SuppressWarnings("unused")
     * @throws DBALException
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('ALTER TABLE logs.user_timesheet_day_logs DROP CONSTRAINT fk_657e6c545b30fbb2');
        $this->addSql('DROP INDEX logs.idx_a0d8d20e5b30fbb2');
        $this->addSql('DROP INDEX logs.idx_user_timesheet_day_log_log_date');
        $this->addSql('ALTER TABLE logs.user_timesheet_day_logs RENAME COLUMN user_timesheet_day_id TO parent_id');
        $this->addSql('ALTER TABLE logs.user_timesheet_day_logs RENAME COLUMN trigger TO trigger_element');
        $this->addSql(<<<'SQL'
ALTER TABLE logs.user_timesheet_day_logs ADD CONSTRAINT FK_A0D8D20E727ACA70
    FOREIGN KEY (parent_id)
    REFERENCES "user_timesheet_days" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql('CREATE INDEX idx_user_timesheet_day_log_parent ON logs.user_timesheet_day_logs (parent_id)');
        $this->addSql('ALTER INDEX logs.idx_a0d8d20e7e3c61f9 RENAME TO idx_user_timesheet_day_log_owner');
    }

    /**
     * @param Schema $schema
     *
     * @return void
     * @SuppressWarnings("unused")
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(true, 'Migration can only be executed safely on \'postgresql\'.');
    }
}
