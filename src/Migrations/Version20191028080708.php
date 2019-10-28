<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\DBALException;

/**
* Class Version20191028080708
*/
final class Version20191028080708 extends AbstractMigration
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

        $this->addSql('ALTER TABLE logs.day_definition_logs DROP CONSTRAINT fk_bd1ef8d081f0c051');
        $this->addSql('DROP INDEX logs.idx_day_definitions_day_definition_log_date');
        $this->addSql('DROP INDEX logs.idx_bd1ef8d081f0c051');
        $this->addSql('ALTER TABLE logs.day_definition_logs RENAME COLUMN day_definition_id TO parent_id');
        $this->addSql('ALTER TABLE logs.day_definition_logs RENAME COLUMN trigger TO trigger_element');
        $this->addSql(<<<'SQL'
ALTER TABLE logs.day_definition_logs ADD CONSTRAINT FK_BD1EF8D0727ACA70
    FOREIGN KEY (parent_id)
        REFERENCES "dictionary"."day_definitions" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql('CREATE INDEX idx_day_definitions_day_definition_log_parent ON logs.day_definition_logs (parent_id)');
        $this->addSql('ALTER INDEX logs.idx_bd1ef8d07e3c61f9 RENAME TO idx_day_definitions_day_definition_log_owner');
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
