<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190924054037 extends AbstractMigration
{
    /**
     * @param Schema $schema
     *
     * @SuppressWarnings("unused")
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
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql(
            <<<SQL
        CREATE TABLE "dictionary"."user_work_schedule_statuses"
            (
                id VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )
SQL
        );
        $this->addSql(
            'CREATE INDEX idx_user_work_schedule_statuses_name ON "dictionary"."user_work_schedule_statuses" (name)'
        );
        $this->addSql(
            <<<SQL
        ALTER TABLE user_timesheet_days ADD CONSTRAINT FK_CF5F9E7D8160D46
        FOREIGN KEY (presence_type_id)
        REFERENCES "dictionary"."presence_types" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql(
            <<<SQL
        ALTER TABLE user_timesheet_days ADD CONSTRAINT FK_CF5F9E7CCAA91B
        FOREIGN KEY (absence_type_id)
        REFERENCES "dictionary"."absence_types" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql('ALTER INDEX logs.idx_user_timesheet_id RENAME TO IDX_BA6C4922B113ABC');
        $this->addSql('ALTER INDEX logs.idx_user_timesheet_owner_id RENAME TO IDX_BA6C4927E3C61F9');
        $this->addSql('ALTER TABLE logs.user_timesheet_day_logs ALTER user_timesheet_day_id SET NOT NULL');
        $this->addSql('ALTER INDEX logs.idx_657e6c545b30fbb2 RENAME TO IDX_A0D8D20E5B30FBB2');
        $this->addSql('ALTER INDEX logs.idx_657e6c547e3c61f9 RENAME TO IDX_A0D8D20E7E3C61F9');
        $this->addSql('DROP INDEX idx_user_work_schedules_status');
        $this->addSql('ALTER TABLE user_work_schedules ADD status_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_work_schedules DROP status');
        $this->addSql(
            <<<SQL
        ALTER TABLE user_work_schedules ADD CONSTRAINT FK_38B6AB826BF700BD
        FOREIGN KEY (status_id)
        REFERENCES "dictionary"."user_work_schedule_statuses" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql(
            <<<SQL
        ALTER TABLE user_work_schedules ADD CONSTRAINT FK_38B6AB8253BD4E7B
        FOREIGN KEY (work_schedule_profile_id)
        REFERENCES "dictionary"."work_schedule_profiles" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql('CREATE INDEX idx_user_work_schedules_status ON user_work_schedules (status_id)');
        $this->addSql(
            <<<SQL
        ALTER TABLE users ADD CONSTRAINT FK_1483A5E9691648CC
        FOREIGN KEY (default_work_schedule_profile_id)
        REFERENCES "dictionary"."work_schedule_profiles" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql(
            'ALTER INDEX logs.idx_user_work_schedule_log_user_work_schedule_id RENAME TO IDX_39F8C792C6DF60CB'
        );
        $this->addSql('ALTER INDEX logs.idx_user_work_schedule_log_owner_id RENAME TO IDX_39F8C7927E3C61F9');
        $this->addSql(
            <<<SQL
        ALTER TABLE user_work_schedule_days ADD CONSTRAINT FK_E73543F281F0C051
        FOREIGN KEY (day_definition_id)
        REFERENCES "dictionary"."day_definitions" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
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
