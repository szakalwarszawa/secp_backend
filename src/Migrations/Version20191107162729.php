<?php
/** @noinspection LongLine */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
* Class Version20191107162729
*/
final class Version20191107162729 extends AbstractMigration
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
     * @return void
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

        $this->addSql(
            <<<'SQL'
UPDATE dictionary.user_timesheet_statuses SET 
    name = 'Edytowana przez pracownika', 
    rules = '"{\"ROLE_USER\":[\"TIMESHEET-STATUS-OWNER-ACCEPT\"],\"ROLE_SECRETARY\":[\"TIMESHEET-STATUS-OWNER-ACCEPT\"],\"ROLE_SECTION_MANAGER\":[\"TIMESHEET-STATUS-OWNER-ACCEPT\"],\"ROLE_DEPARTMENT_MANAGER\":[\"TIMESHEET-STATUS-OWNER-ACCEPT\",\"TIMESHEET-STATUS-MANAGER-ACCEPT\"],\"ROLE_HR\":[\"TIMESHEET-STATUS-OWNER-ACCEPT\",\"TIMESHEET-STATUS-MANAGER-ACCEPT\",\"TIMESHEET-STATUS-HR-ACCEPT\"]}"' 
WHERE id = 'TIMESHEET-STATUS-OWNER-EDIT';
SQL
        );
        $this->addSql(<<<'SQL'
UPDATE dictionary.user_timesheet_statuses SET
    name = 'Zatwierdzona przez pracownika',
    rules = '"{\"ROLE_DEPARTMENT_MANAGER\":[\"TIMESHEET-STATUS-OWNER-EDIT\",\"TIMESHEET-STATUS-MANAGER-ACCEPT\"],\"ROLE_HR\":[\"TIMESHEET-STATUS-OWNER-EDIT\",\"TIMESHEET-STATUS-MANAGER-ACCEPT\",\"TIMESHEET-STATUS-HR-ACCEPT\"]}"'
WHERE id = 'TIMESHEET-STATUS-OWNER-ACCEPT';
SQL
        );
        $this->addSql(<<<'SQL'
UPDATE dictionary.user_timesheet_statuses SET
    name = 'Zatwierdzona przez przełożonego',
    rules = '"{\"ROLE_HR\":[\"TIMESHEET-STATUS-OWNER-EDIT\",\"TIMESHEET-STATUS-OWNER-ACCEPT\",\"TIMESHEET-STATUS-MANAGER-ACCEPT\",\"TIMESHEET-STATUS-HR-ACCEPT\"]}"'
WHERE id = 'TIMESHEET-STATUS-MANAGER-ACCEPT';
SQL
        );
        $this->addSql(<<<'SQL'
UPDATE dictionary.user_timesheet_statuses SET
    name = 'Zatwierdzona przez HR',
    rules = '"{\"ROLE_HR\":[\"TIMESHEET-STATUS-OWNER-EDIT\",\"TIMESHEET-STATUS-OWNER-ACCEPT\",\"TIMESHEET-STATUS-MANAGER-ACCEPT\"]}"'
WHERE id = 'TIMESHEET-STATUS-HR-ACCEPT';
SQL
        );
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
        $this->abortIf(false, 'Downgrade migration can only be executed by next migration.');
    }
}
