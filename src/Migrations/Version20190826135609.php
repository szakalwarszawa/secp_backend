<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190826135609 extends AbstractMigration
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
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('CREATE SEQUENCE "user_timesheet_day_logs_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "user_timesheet_day_logs" (id INT NOT NULL, user_timesheet_day_id integer, 
        owner_id INT NOT NULL, log_date VARCHAR(10) NOT NULL, notice TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_657E6C545B30FBB2 ON "user_timesheet_day_logs" (user_timesheet_day_id)');
        $this->addSql('CREATE INDEX IDX_657E6C547E3C61F9 ON "user_timesheet_day_logs" (owner_id)');
        $this->addSql('CREATE INDEX idx_user_timesheet_day_log_date ON "user_timesheet_day_logs" (log_date)');
        $this->addSql('CREATE INDEX idx_user_timesheet_day_log_log_date ON "user_timesheet_day_logs" 
        (user_timesheet_day_id, log_date)');
        $this->addSql('ALTER TABLE "user_timesheet_day_logs" ADD CONSTRAINT FK_657E6C545B30FBB2 FOREIGN KEY 
        (user_timesheet_day_id) REFERENCES "user_timesheet_days" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user_timesheet_day_logs" ADD CONSTRAINT FK_657E6C547E3C61F9 FOREIGN KEY
        (owner_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_timesheet_days ADD notice VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\DBALException
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE "user_timesheet_day_logs_id_seq" CASCADE');
        $this->addSql('DROP TABLE "user_timesheet_day_logs"');
        $this->addSql('ALTER TABLE "user_timesheet_days" DROP notice');
    }
}
