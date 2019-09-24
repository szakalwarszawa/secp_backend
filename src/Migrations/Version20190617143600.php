<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190617143600 extends AbstractMigration
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
     * @return void
     * @throws DBALException
     * @SuppressWarnings("unused")
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('CREATE SEQUENCE "user_work_schedule_days_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_work_schedules_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            <<<SQL
CREATE TABLE "user_work_schedule_days"
(
    id INT NOT NULL,
    user_work_schedule_id INT NOT NULL,
    day_definition_id CHAR(10) NOT NULL,
    working_day BOOLEAN NOT NULL,
    day_start_time_from VARCHAR(5) NOT NULL DEFAULT '07:30',
    day_start_time_to VARCHAR(5) NOT NULL DEFAULT '07:30',
    day_end_time_from VARCHAR(5) NOT NULL DEFAULT '16:30',
    day_end_time_to VARCHAR(5) NOT NULL DEFAULT '16:30',
    daily_working_time NUMERIC(4, 2) NOT NULL DEFAULT 8.00,
    PRIMARY KEY(id)
)
SQL
        );
        $this->addSql(
            <<<SQL
CREATE INDEX IDX_E73543F281F0C051 ON "user_work_schedule_days" (day_definition_id)
SQL
        );
        $this->addSql(
            <<<SQL
CREATE INDEX idx_user_work_schedule_days_user_work_schedule_id ON "user_work_schedule_days" (user_work_schedule_id)
SQL
        );
        $this->addSql(
            'CREATE INDEX idx_user_work_schedule_days_working_day ON "user_work_schedule_days" (working_day)'
        );
        $this->addSql(
            <<<SQL
CREATE TABLE "user_work_schedules"
(
    id INT NOT NULL,
    owner_id INT NOT NULL,
    work_schedule_profile_id INT NOT NULL,
    from_date DATE NOT NULL,
    to_date DATE NOT NULL,
    status INT NOT NULL,
    PRIMARY KEY(id)
)
SQL
        );
        $this->addSql('CREATE INDEX idx_user_work_schedules_status ON "user_work_schedules" (status)');
        $this->addSql('CREATE INDEX idx_user_work_schedules_from_date ON "user_work_schedules" (from_date)');
        $this->addSql('CREATE INDEX idx_user_work_schedules_to_date ON "user_work_schedules" (to_date)');
        $this->addSql('CREATE INDEX idx_user_work_schedules_owner_id ON "user_work_schedules" (owner_id)');
        $this->addSql(
            <<<SQL
CREATE INDEX idx_user_work_schedules_work_schedule_profile_id ON "user_work_schedules" (work_schedule_profile_id)
SQL
        );
        $this->addSql(
            <<<SQL
ALTER TABLE "user_work_schedule_days"
    ADD CONSTRAINT FK_E73543F2C6DF60CB
    FOREIGN KEY (user_work_schedule_id)
    REFERENCES "user_work_schedules" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql(
            <<<SQL
ALTER TABLE "user_work_schedule_days"
    ADD CONSTRAINT FK_E73543F281F0C051
    FOREIGN KEY (day_definition_id)
    REFERENCES "day_definitions" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql(
            <<<SQL
ALTER TABLE "user_work_schedules"
    ADD CONSTRAINT FK_38B6AB827E3C61F9
    FOREIGN KEY (owner_id)
    REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql(
            <<<SQL
ALTER TABLE "user_work_schedules"
    ADD CONSTRAINT FK_38B6AB8253BD4E7B
    FOREIGN KEY (work_schedule_profile_id)
    REFERENCES "work_schedule_profiles" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql('ALTER TABLE users ALTER default_work_schedule_profile_id DROP DEFAULT');
    }

    /**
     * @param Schema $schema
     * @return void
     * @SuppressWarnings("unused")
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(true, 'Downgrade migration can only be executed by next migration.');
    }
}
