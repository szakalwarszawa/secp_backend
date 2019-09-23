<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190917052713 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws DBALException
     * @SuppressWarnings("unused")
     */
    public function getDescription() : string
    {
        return '';
    }

    /**
     * @param Schema $schema
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

        $this->addSql('ALTER TABLE logs.ldap_import_log RENAME COLUMN result TO import_result');
    }

    /**
     * @param Schema $schema
     *
     * @SuppressWarnings("unused")
     *
     * @return void
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf(true, 'Downgrade migration can only be executed by next migration.');
    }
}
