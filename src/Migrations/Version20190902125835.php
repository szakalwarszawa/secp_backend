<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190902125835 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('DROP SEQUENCE user_timesheet_logs_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE "user_work_schedule_logs_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "user_work_schedule_logs" (id INT NOT NULL, user_work_schedule_id INTEGER NOT NULL, owner_id INT NOT NULL, log_date VARCHAR(10) NOT NULL, notice TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FC5E79C8C6DF60CB ON "user_work_schedule_logs" (user_work_schedule_id)');
        $this->addSql('CREATE INDEX IDX_FC5E79C87E3C61F9 ON "user_work_schedule_logs" (owner_id)');
        $this->addSql('CREATE INDEX idx_user_work_schedule_log_date ON "user_work_schedule_logs" (log_date)');
        $this->addSql('CREATE INDEX idx_user_work_schedule_user_work_schedule_log_date ON "user_work_schedule_logs" (user_work_schedule_id, log_date)');
        $this->addSql('ALTER TABLE "user_work_schedule_logs" ADD CONSTRAINT FK_FC5E79C8C6DF60CB FOREIGN KEY (user_work_schedule_id) REFERENCES "user_work_schedules" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user_work_schedule_logs" ADD CONSTRAINT FK_FC5E79C87E3C61F9 FOREIGN KEY (owner_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE user_timesheet_logs');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf(true, 'Downgrade migration can only be executed by next migration.');
    }
}
