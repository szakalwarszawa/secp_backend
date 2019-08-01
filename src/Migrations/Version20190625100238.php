<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190625100238 extends AbstractMigration
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
     * @return void
     * @throws DBALException
     * @SuppressWarnings("unused")
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('CREATE SEQUENCE "user_timesheet_days_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_timesheets_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            'CREATE TABLE "user_timesheet_days" (
                    id INT NOT NULL,
                    user_timesheet_id INT NOT NULL,
                    user_work_schedule_day_id INT NOT NULL,
                    presence_type_id INT NOT NULL,
                    absence_type_id INT DEFAULT NULL,
                    day_start_time VARCHAR(5) DEFAULT NULL,
                    day_end_time VARCHAR(5) DEFAULT NULL,
                    working_time NUMERIC(4, 2) NOT NULL,
                    PRIMARY KEY(id)
                )'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CF5F9E74731ACF4 ON "user_timesheet_days" (user_work_schedule_day_id)');
        $this->addSql(
            'CREATE INDEX idx_user_timesheet_days_absence_type_id ON "user_timesheet_days" (absence_type_id)'
        );
        $this->addSql(
            'CREATE INDEX idx_user_timesheet_days_presence_type_id ON "user_timesheet_days" (presence_type_id)'
        );
        $this->addSql(
            'CREATE INDEX idx_user_timesheet_days_user_timesheet_id ON "user_timesheet_days" (user_timesheet_id)'
        );
        $this->addSql(
            'CREATE INDEX idx_user_timesheet_days_user_work_schedule_day_id ON "user_timesheet_days" (user_work_schedule_day_id)'
        );
        $this->addSql(
            'CREATE TABLE "user_timesheets" (
                    id INT NOT NULL,
                    owner_id INT NOT NULL,
                    period VARCHAR(7) NOT NULL,
                    status INT NOT NULL,
                    PRIMARY KEY(id)
                )'
        );
        $this->addSql('CREATE INDEX idx_user_timesheets_owner_id ON "user_timesheets" (owner_id)');
        $this->addSql('CREATE INDEX idx_user_timesheets_short_name ON "user_timesheets" (period)');
        $this->addSql('CREATE INDEX idx_user_timesheets_status ON "user_timesheets" (status)');
        $this->addSql(
            'ALTER TABLE "user_timesheet_days"
                    ADD CONSTRAINT FK_CF5F9E72B113ABC FOREIGN KEY (user_timesheet_id)
                        REFERENCES "user_timesheets" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE "user_timesheet_days"
                    ADD CONSTRAINT FK_CF5F9E74731ACF4 FOREIGN KEY (user_work_schedule_day_id)
                        REFERENCES "user_work_schedule_days" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE "user_timesheet_days"
                    ADD CONSTRAINT FK_CF5F9E7D8160D46 FOREIGN KEY (presence_type_id)
                        REFERENCES "presence_types" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE "user_timesheet_days"
                    ADD CONSTRAINT FK_CF5F9E7CCAA91B FOREIGN KEY (absence_type_id)
                        REFERENCES "absence_types" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE "user_timesheets"
                    ADD CONSTRAINT FK_3DA0BBC27E3C61F9 FOREIGN KEY (owner_id)
                        REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql('ALTER TABLE user_work_schedule_days ADD user_timesheet_day_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE user_work_schedule_days
                    ADD CONSTRAINT FK_E73543F25B30FBB2 FOREIGN KEY (user_timesheet_day_id)
                        REFERENCES "user_timesheet_days" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'CREATE UNIQUE INDEX idx_user_work_schedule_days_user_work_user_timesheet_day_id
                    ON user_work_schedule_days (user_timesheet_day_id)'
        );
    }

    /**
     * @param Schema $schema
     * @return void
     * @SuppressWarnings("unused")
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(true, 'Downgrade migration can only be executed by next migration.');
    }
}
