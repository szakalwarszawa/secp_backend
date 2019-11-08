<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\DBALException;

/**
* Class Version20191029063456
*/
final class Version20191029063456 extends AbstractMigration
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

     * @throws DBALException

     * @return void

     * @SuppressWarnings("unused")
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('ALTER TABLE logs.user_timesheet_logs DROP CONSTRAINT fk_ba6c4922b113abc');
        $this->addSql('DROP INDEX logs.idx_ba6c4922b113abc');
        $this->addSql('DROP INDEX logs.idx_user_timesheet_log_user_timesheet_log_date');
        $this->addSql('ALTER TABLE logs.user_timesheet_logs RENAME COLUMN user_timesheet_id TO parent_id');
        $this->addSql('ALTER TABLE logs.user_timesheet_logs RENAME COLUMN trigger TO trigger_element');
        $this->addSql(<<<'SQL'
ALTER TABLE logs.user_timesheet_logs ADD CONSTRAINT FK_BA6C492727ACA70
    FOREIGN KEY (parent_id)
        REFERENCES "user_timesheets" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql('CREATE INDEX idx_user_timesheet_log_parent ON logs.user_timesheet_logs (parent_id)');
        $this->addSql('ALTER INDEX logs.idx_ba6c4927e3c61f9 RENAME TO idx_user_timesheet_log_owner');
    }

    /**
     * @param Schema $schema

     * @return void

     * @SuppressWarnings("unused")
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(true, 'Migration can only be executed safely on \'postgresql\'.');
    }
}
