<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Class Version20190909162439
 * @package DoctrineMigrations
 */
final class Version20190909162439 extends AbstractMigration
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
     * @throws \Doctrine\DBAL\DBALException
     * @SuppressWarnings("unused")
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );
        $this->addSql('CREATE SEQUENCE "logs"."user_timesheet_logs_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            <<<SQL
        CREATE TABLE "logs"."user_timesheet_logs" (id INT NOT NULL, user_timesheet_id integer NOT NULL,
        owner_id INT NOT NULL, log_date VARCHAR(10) NOT NULL, notice TEXT NOT NULL, PRIMARY KEY(id))
SQL
        );
        $this->addSql('CREATE INDEX idx_user_timesheet_id ON "logs"."user_timesheet_logs" (user_timesheet_id)');
        $this->addSql('CREATE INDEX idx_user_timesheet_owner_id ON "logs"."user_timesheet_logs" (owner_id)');
        $this->addSql('CREATE INDEX idx_user_timesheet_log_date ON "logs"."user_timesheet_logs" (log_date)');
        $this->addSql(
            <<<SQL
        CREATE INDEX idx_user_timesheet_log_user_timesheet_log_date ON "logs"."user_timesheet_logs"
        (user_timesheet_id, log_date)
SQL
        );
        $this->addSql(
            <<<SQL
        ALTER TABLE "logs"."user_timesheet_logs" ADD CONSTRAINT FK_BA6C4922B113ABC FOREIGN KEY (user_timesheet_id)
        REFERENCES "user_timesheets" (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
SQL
        );
        $this->addSql(
            <<<SQL
        ALTER TABLE "logs"."user_timesheet_logs" ADD CONSTRAINT FK_BA6C4927E3C61F9 FOREIGN KEY (owner_id) REFERENCES
         "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
SQL
        );
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\DBALException
     * @SuppressWarnings("unused")
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );
    }
}
