<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190903070405 extends AbstractMigration
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

        $this->addSql('CREATE SCHEMA dictionary');
        $this->addSql('CREATE SEQUENCE "dictionary"."roles_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            'CREATE TABLE "dictionary"."roles" (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('DROP INDEX idx_property_based_roles_framework_value');
        $this->addSql('ALTER TABLE property_based_roles ADD role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE property_based_roles DROP framework_value');

        $this->addSql(
            <<<SQL
ALTER TABLE property_based_roles ADD CONSTRAINT FK_41676BACD60322AC
FOREIGN KEY (role_id)
REFERENCES "dictionary"."roles" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql('CREATE INDEX idx_property_based_roles_role_id ON property_based_roles (role_id)');
    }

    /**
     * @param Schema $schema
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(true, 'Downgrade migration can only be executed by next migration.');
    }
}
