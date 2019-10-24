<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Class Version20190909174313
 * @package DoctrineMigrations
 */
final class Version20190909174313 extends AbstractMigration
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
        $this->addSql('CREATE SEQUENCE "logs"."user_work_schedule_logs_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            <<<SQL
        CREATE TABLE "logs"."user_work_schedule_logs" (id INT NOT NULL, user_work_schedule_id INTEGER NOT NULL, owner_id
        INT NOT NULL, log_date VARCHAR(10) NOT NULL, notice TEXT NOT NULL, PRIMARY KEY(id))
SQL
        );
        $this->addSql(
            <<<SQL
        CREATE INDEX idx_user_work_schedule_log_user_work_schedule_id ON "logs"."user_work_schedule_logs"
        (user_work_schedule_id)
SQL
        );
        $this->addSql(
            <<<SQL
        CREATE INDEX idx_user_work_schedule_log_owner_id ON "logs"."user_work_schedule_logs" (owner_id)
SQL
        );
        $this->addSql(
            <<<SQL
        CREATE INDEX idx_user_work_schedule_log_date ON "logs"."user_work_schedule_logs" (log_date)
SQL
        );
        $this->addSql(
            <<<SQL
        CREATE INDEX idx_user_work_schedule_user_work_schedule_log_date ON "logs"."user_work_schedule_logs"
        (user_work_schedule_id, log_date)
SQL
        );
        $this->addSql(
            <<<SQL
        ALTER TABLE "logs"."user_work_schedule_logs" ADD CONSTRAINT FK_39F8C792C6DF60CB FOREIGN KEY
         (user_work_schedule_id) REFERENCES "user_work_schedules" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql(
            <<<SQL
        ALTER TABLE "logs"."user_work_schedule_logs" ADD CONSTRAINT FK_39F8C7927E3C61F9 FOREIGN KEY (owner_id)
        REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
    }

    /**
     * @param Schema $schema
     *
     * @SuppressWarnings("unused")
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(true, 'Downgrade migration can only be executed by next migration.');
    }
}
