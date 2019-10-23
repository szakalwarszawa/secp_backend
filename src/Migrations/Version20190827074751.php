<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190827074751 extends AbstractMigration
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
     *
     * @return void
     *
     * @throws DBALException
     *
     * @SuppressWarnings("unused")
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('CREATE SEQUENCE "property_based_roles_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            <<<SQL
CREATE TABLE "property_based_roles"
(
    id INT NOT NULL,
    ldap_value VARCHAR(255) DEFAULT NULL,
    framework_value VARCHAR(255) NOT NULL,
    overridable BOOLEAN NOT NULL,
    based_on VARCHAR(255) NOT NULL,
    PRIMARY KEY(id)
)
SQL
        );


        $this->addSql('CREATE INDEX idx_property_based_roles_ldap_value ON "property_based_roles" (ldap_value)');
        $this->addSql(
            <<<SQL
CREATE INDEX idx_property_based_roles_framework_value ON "property_based_roles" (framework_value)
SQL
        );
        $this->addSql('CREATE INDEX idx_property_based_roles_overridable ON "property_based_roles" (based_on)');
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
