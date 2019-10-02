<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190924080935 extends AbstractMigration
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
        $this->addSql(
            <<<SQL
CREATE TABLE "dictionary"."user_timesheet_statuses"
    (
        id VARCHAR(255) NOT NULL,
        name VARCHAR(255) NOT NULL,
        PRIMARY KEY(id)
    )
SQL
        );
        $this->addSql(
            'CREATE INDEX idx_user_timsheet_statuses_name ON "dictionary"."user_timesheet_statuses" (name)'
        );
        $this->addSql('DROP INDEX idx_user_timesheets_status');
        $this->addSql('ALTER TABLE user_timesheets ADD status_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_timesheets DROP status');
        $this->addSql(
            <<<SQL
ALTER TABLE user_timesheets ADD CONSTRAINT FK_3DA0BBC26BF700BD
FOREIGN KEY (status_id)
REFERENCES "dictionary"."user_timesheet_statuses" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql('CREATE INDEX idx_user_timesheets_status ON user_timesheets (status_id)');
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
