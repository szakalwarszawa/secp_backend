<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\DBALException;

/**
* Class Version20191029064955
*/
final class Version20191029064955 extends AbstractMigration
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

        $this->addSql('ALTER TABLE logs.user_work_schedule_logs DROP CONSTRAINT fk_39f8c792c6df60cb');
        $this->addSql('DROP INDEX logs.idx_39f8c792c6df60cb');
        $this->addSql('DROP INDEX logs.idx_user_work_schedule_user_work_schedule_log_date');
        $this->addSql('ALTER TABLE logs.user_work_schedule_logs RENAME COLUMN user_work_schedule_id TO parent_id');
        $this->addSql('ALTER TABLE logs.user_work_schedule_logs RENAME COLUMN trigger TO trigger_element');
        $this->addSql(<<<'SQL'
ALTER TABLE logs.user_work_schedule_logs ADD CONSTRAINT FK_39F8C792727ACA70
    FOREIGN KEY (parent_id)
        REFERENCES "user_work_schedules" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql('CREATE INDEX idx_user_work_schedule_user_parent ON logs.user_work_schedule_logs (parent_id)');
        $this->addSql('ALTER INDEX logs.idx_39f8c7927e3c61f9 RENAME TO idx_user_work_schedule_user_owner');
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
