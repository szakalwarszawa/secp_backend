<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190614151557 extends AbstractMigration
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

        $this->addSql(
            'ALTER TABLE work_schedule_profiles ADD day_start_time_from VARCHAR(5) NOT NULL DEFAULT \'07:30\''
        );
        $this->addSql('ALTER TABLE work_schedule_profiles ADD day_start_time_to VARCHAR(5) NOT NULL DEFAULT \'07:30\'');
        $this->addSql('ALTER TABLE work_schedule_profiles ADD day_end_time_from VARCHAR(5) NOT NULL DEFAULT \'16:30\'');
        $this->addSql('ALTER TABLE work_schedule_profiles ADD day_end_time_to VARCHAR(5) NOT NULL DEFAULT \'16:30\'');
        $this->addSql('ALTER TABLE work_schedule_profiles ADD daily_working_time NUMERIC(4, 2) NOT NULL DEFAULT 8.00');
        $this->addSql('ALTER TABLE day_definition_logs ALTER log_date TYPE VARCHAR(10)');
        $this->addSql('ALTER TABLE day_definition_logs ALTER log_date DROP DEFAULT');
        $this->addSql('ALTER TABLE users ADD day_start_time_from VARCHAR(5) NOT NULL DEFAULT \'07:30\'');
        $this->addSql('ALTER TABLE users ADD day_start_time_to VARCHAR(5) NOT NULL DEFAULT \'07:30\'');
        $this->addSql('ALTER TABLE users ADD day_end_time_from VARCHAR(5) NOT NULL DEFAULT \'16:30\'');
        $this->addSql('ALTER TABLE users ADD day_end_time_to VARCHAR(5) NOT NULL DEFAULT \'16:30\'');
        $this->addSql('ALTER TABLE users ADD daily_working_time NUMERIC(4, 2) NOT NULL DEFAULT 8.00');
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
