<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190611101134 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE "day_definition_logs_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "day_definitions" (id char(10) NOT NULL, working_day BOOLEAN NOT NULL, notice VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_day_definitions_working_day ON "day_definitions" (working_day)');
        $this->addSql('CREATE INDEX idx_day_definitions_date_working_day ON "day_definitions" (id, working_day)');
        $this->addSql('CREATE TABLE "day_definition_logs" (id INT NOT NULL, day_definition_id char(10) NOT NULL, owner_id INT NOT NULL, log_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, notice TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A126FF9F81F0C051 ON "day_definition_logs" (day_definition_id)');
        $this->addSql('CREATE INDEX IDX_A126FF9F7E3C61F9 ON "day_definition_logs" (owner_id)');
        $this->addSql('CREATE INDEX idx_day_definitions_log_date ON "day_definition_logs" (log_date)');
        $this->addSql('CREATE INDEX idx_day_definitions_day_definition_log_date ON "day_definition_logs" (day_definition_id, log_date)');
        $this->addSql('COMMENT ON COLUMN "day_definition_logs".log_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE "day_definition_logs" ADD CONSTRAINT FK_A126FF9F81F0C051 FOREIGN KEY (day_definition_id) REFERENCES "day_definitions" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "day_definition_logs" ADD CONSTRAINT FK_A126FF9F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf(true, 'Downgrade migration can only be executed by next migration.');
    }
}
