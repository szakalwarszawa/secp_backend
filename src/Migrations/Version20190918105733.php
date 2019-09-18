<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190918105733 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription() : string
    {
        return '';
    }

    /**
     * @param Schema $schema
     *
     * @throws DBALException
     *
     * @SuppressWarnings("unused")
     *
     * @return void
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE logs.ldap_import_log_id_seq CASCADE');
        $this->addSql('DROP TABLE logs.ldap_import_log');
        $this->addSql('CREATE SEQUENCE "logs"."import_ldap_logs_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            <<<SQL
        CREATE TABLE "logs"."import_ldap_logs"
            (
                id INT NOT NULL,
                resource_name TEXT NOT NULL,
                succeed_elements JSON DEFAULT NULL,
                failed_elements JSON DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
SQL
        );

        $this->addSql('CREATE INDEX idx_import_ldap_log_resource_name ON "logs"."import_ldap_logs" (resource_name)');
        $this->addSql('CREATE INDEX idx_import_ldap_log_created_at ON logs.import_ldap_logs (created_at)');
        $this->addSql('COMMENT ON COLUMN "logs"."import_ldap_logs".succeed_elements IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN "logs"."import_ldap_logs".failed_elements IS \'(DC2Type:json_array)\'');
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\DBALException
     * @SuppressWarnings("unused")
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(true, 'Downgrade migration can only be executed by next migration.');
    }
}
