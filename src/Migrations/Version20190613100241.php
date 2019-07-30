<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190613100241 extends AbstractMigration
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
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('CREATE SEQUENCE "work_schedule_profiles_id_seq" INCREMENT BY 1 MINVALUE 1 START 6');
        $this->addSql(
            'CREATE TABLE "work_schedule_profiles" (id INT NOT NULL, name VARCHAR(255) NOT NULL, notice VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX idx_work_schedule_profiles_name ON "work_schedule_profiles" (name)');
        $this->addSql('COMMENT ON COLUMN day_definition_logs.log_date IS NULL');

        $this->addSql(
            <<<'SQL'
INSERT INTO "work_schedule_profiles" ("id", "name", "notice") VALUES 
(1, 'Domyślny', null),
(2, 'Indywidualny', null),
(3, 'Ruchomy', null),
(4, 'Harmonogram', null),
(5, 'Brak', null);
SQL
        );

        $this->addSql('ALTER TABLE users ADD default_work_schedule_profile_id INT NOT NULL DEFAULT 1');
        $this->addSql(
            'ALTER TABLE users ADD CONSTRAINT FK_1483A5E9691648CC FOREIGN KEY (default_work_schedule_profile_id) REFERENCES "work_schedule_profiles" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'CREATE INDEX idx_users_default_work_schedule_profile_id ON users (default_work_schedule_profile_id)'
        );
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(true, 'Downgrade migration can only be executed by next migration.');
    }
}
