<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190809080518 extends AbstractMigration
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
     * @return void
     * @throws DBALException
     * @SuppressWarnings("unused")
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('CREATE SCHEMA logs');
        $this->addSql('CREATE SEQUENCE "logs"."ldap_import_log_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            <<<SQL
CREATE TABLE "logs"."ldap_import_log"
(
    id INT NOT NULL,
    result TEXT NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    PRIMARY KEY(id)
)
SQL
        );
    }

    /**
     * @param Schema $schema
     * @return void
     * @SuppressWarnings("unused")
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf(true, 'Downgrade migration can only be executed by next migration.');
    }
}
