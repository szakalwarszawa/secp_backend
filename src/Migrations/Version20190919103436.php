<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190919103436 extends AbstractMigration
{
    /**
     * @param Schema $schema
     *
     * @SuppressWarnings("unused")
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @param Schema $schema
     *
     * @throws DBALException
     *
     * @SuppressWarnings("unused")
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('ALTER TABLE day_definition_logs DROP CONSTRAINT fk_a126ff9f81f0c051');
        $this->addSql('ALTER TABLE user_work_schedule_days DROP CONSTRAINT fk_e73543f281f0c051');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT fk_1483a5e9691648cc');
        $this->addSql('ALTER TABLE user_work_schedules DROP CONSTRAINT fk_38b6ab8253bd4e7b');
        $this->addSql('ALTER TABLE user_timesheet_days DROP CONSTRAINT fk_cf5f9e7ccaa91b');
        $this->addSql('ALTER TABLE user_timesheet_days DROP CONSTRAINT fk_cf5f9e7d8160d46');
        $this->addSql('DROP SEQUENCE day_definition_logs_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE work_schedule_profiles_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE absence_types_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE presence_types_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE property_based_roles_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE "dictionary"."property_based_roles_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "logs"."day_definition_logs_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "dictionary"."absence_types_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "dictionary"."presence_types_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            'CREATE SEQUENCE "dictionary"."work_schedule_profiles_id_seq" INCREMENT BY 1 MINVALUE 1 START 1'
        );
        $this->addSql(
            <<<SQL
CREATE TABLE "dictionary"."property_based_roles" (
    id INT NOT NULL,
    role_id INT DEFAULT NULL,
    ldap_value VARCHAR(255) DEFAULT NULL,
    overridable BOOLEAN NOT NULL,
    based_on VARCHAR(255) NOT NULL,
    PRIMARY KEY(id)
    )
SQL
        );
        $this->addSql(
            'CREATE INDEX idx_property_based_roles_ldap_value ON "dictionary"."property_based_roles" (ldap_value)'
        );
        $this->addSql(
            'CREATE INDEX idx_property_based_roles_role_id ON "dictionary"."property_based_roles" (role_id)'
        );
        $this->addSql(
            'CREATE INDEX idx_property_based_roles_overridable ON "dictionary"."property_based_roles" (based_on)'
        );
        $this->addSql(
            <<<SQL
CREATE TABLE "dictionary"."day_definitions" (
    id CHAR(10) NOT NULL,
    working_day BOOLEAN NOT NULL,
    notice VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY(id)
    )
SQL
        );
        $this->addSql('CREATE INDEX idx_day_definitions_working_day ON "dictionary"."day_definitions" (working_day)');
        $this->addSql(
            'CREATE INDEX idx_day_definitions_date_working_day ON "dictionary"."day_definitions" (id, working_day)'
        );
        $this->addSql(
            <<<SQL
CREATE TABLE "logs"."day_definition_logs" (
    id INT NOT NULL,
    day_definition_id CHAR(10) NOT NULL,
    owner_id INT NOT NULL,
    log_date VARCHAR(10) NOT NULL,
    notice TEXT NOT NULL,
    PRIMARY KEY(id)
    )
SQL
        );
        $this->addSql('CREATE INDEX IDX_BD1EF8D081F0C051 ON "logs"."day_definition_logs" (day_definition_id)');
        $this->addSql('CREATE INDEX IDX_BD1EF8D07E3C61F9 ON "logs"."day_definition_logs" (owner_id)');
        $this->addSql('CREATE INDEX idx_day_definitions_log_date ON "logs"."day_definition_logs" (log_date)');
        $this->addSql(
            <<<SQL
CREATE INDEX idx_day_definitions_day_definition_log_date ON "logs"."day_definition_logs" (day_definition_id, log_date)
SQL
        );
        $this->addSql(
            <<<SQL
CREATE TABLE "dictionary"."absence_types" (
    id INT NOT NULL,
    short_name VARCHAR(10) NOT NULL,
    name VARCHAR(255) NOT NULL,
    active BOOLEAN NOT NULL,
    PRIMARY KEY(id)
    )
SQL
        );
        $this->addSql('CREATE INDEX idx_absence_types_short_name ON "dictionary"."absence_types" (short_name)');
        $this->addSql('CREATE INDEX idx_absence_types_name ON "dictionary"."absence_types" (active, name)');
        $this->addSql('CREATE INDEX idx_absence_types_active ON "dictionary"."absence_types" (active)');
        $this->addSql(
            <<<SQL
CREATE TABLE "dictionary"."presence_types" (
    id INT NOT NULL,
    short_name VARCHAR(10) NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_absence BOOLEAN DEFAULT FALSE NOT NULL,
    is_timed BOOLEAN DEFAULT TRUE NOT NULL,
    active BOOLEAN NOT NULL,
    create_restriction INT DEFAULT 0 NOT NULL,
    edit_restriction INT DEFAULT 0 NOT NULL,
    working_day_restriction INT DEFAULT 0 NOT NULL,
    PRIMARY KEY(id)
    )
SQL
        );
        $this->addSql('CREATE INDEX idx_presence_types_short_name ON "dictionary"."presence_types" (short_name)');
        $this->addSql('CREATE INDEX idx_presence_types_name ON "dictionary"."presence_types" (active, name)');
        $this->addSql('CREATE INDEX idx_presence_types_active ON "dictionary"."presence_types" (active)');
        $this->addSql(
            <<<SQL
CREATE TABLE "dictionary"."work_schedule_profiles" (
    id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    notice VARCHAR(255) DEFAULT NULL,
    day_start_time_from VARCHAR(5) NOT NULL DEFAULT '07:30',
    day_start_time_to VARCHAR(5) NOT NULL DEFAULT '07:30',
    day_end_time_from VARCHAR(5) NOT NULL DEFAULT '16:30',
    day_end_time_to VARCHAR(5) NOT NULL DEFAULT '16:30',
    daily_working_time NUMERIC(4, 2) NOT NULL DEFAULT 8.00,
    PRIMARY KEY(id)
    )
SQL
        );
        $this->addSql('CREATE INDEX idx_work_schedule_profiles_name ON "dictionary"."work_schedule_profiles" (name)');
        $this->addSql(
            <<<SQL
ALTER TABLE "dictionary"."property_based_roles" ADD CONSTRAINT FK_21720AC6D60322AC
FOREIGN KEY (role_id)
REFERENCES "dictionary"."roles" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql(
            <<<SQL
ALTER TABLE "logs"."day_definition_logs" ADD CONSTRAINT FK_BD1EF8D081F0C051
FOREIGN KEY (day_definition_id)
REFERENCES "dictionary"."day_definitions" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql(
            <<<SQL
ALTER TABLE "logs"."day_definition_logs" ADD CONSTRAINT FK_BD1EF8D07E3C61F9
FOREIGN KEY (owner_id)
REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql('DROP TABLE day_definition_logs');
        $this->addSql('DROP TABLE day_definitions');
        $this->addSql('DROP TABLE work_schedule_profiles');
        $this->addSql('DROP TABLE absence_types');
        $this->addSql('DROP TABLE presence_types');
        $this->addSql('DROP TABLE property_based_roles');
    }

    /**
     * @param Schema $schema
     *
     * @SuppressWarnings("unused")
     *
     * @return void
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(true, 'Downgrade migration can only be executed by next migration.');
    }
}
