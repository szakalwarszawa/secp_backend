<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Class Version20190926111636
 * @package DoctrineMigrations
 */
final class Version20190926111636 extends AbstractMigration
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
     * @return void
     * @SuppressWarnings("unused")
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );
        $this->addSql(
            <<< SQL
        ALTER TABLE "logs"."user_timesheet_logs" ALTER COLUMN log_date type timestamp without time zone USING
        log_date::timestamp without time zone;
SQL
        );
    }

    /**
     * @param Schema $schema
     * @return void
     * @SuppressWarnings("unused")
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf(true, 'Migration can only be executed safely on \'postgresql\'.');
    }
}
