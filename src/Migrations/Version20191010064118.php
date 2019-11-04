<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\DBALException;

/**
 * Class Version20191010064118
 */
final class Version20191010064118 extends AbstractMigration
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
     * @return void
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

        $this->addSql('ALTER TABLE logs.user_timesheet_logs ADD trigger VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE logs.user_timesheet_day_logs ADD trigger VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE logs.user_work_schedule_logs ADD trigger VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE logs.day_definition_logs ADD trigger VARCHAR(100) DEFAULT NULL');
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
        $this->abortIf(true, 'Migration can only be executed safely on \'postgresql\'.');
    }
}
