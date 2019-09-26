<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Class Version20190923085150
 * @package DoctrineMigrations
 */
final class Version20190923085150 extends AbstractMigration
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
     * @return void
     * @throws DBALException
     * @SuppressWarnings("unused")
     */
    public function up(Schema $schema) : void
    {
        $this->addSql(
            <<< SQL
        ALTER TABLE "logs"."day_definition_logs" ALTER COLUMN log_date type timestamp without time zone USING
        log_date::timestamp without time zone;
SQL
        );
        $this->addSql(
            <<< SQL
        ALTER TABLE "logs"."user_timesheet_day_logs" ALTER COLUMN log_date type timestamp without time zone USING
        log_date::timestamp without time zone;
SQL
        );
        $this->addSql(
            <<< SQL
        ALTER TABLE "logs"."user_work_schedule_logs" ALTER COLUMN log_date type timestamp without time zone USING
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
        $this->abortIf(true, 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
    }
}
