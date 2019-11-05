<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\DBALException;

/**
* Class Version20191030074145
*/
final class Version20191030074145 extends AbstractMigration
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

     * @throws DBALException

     * @return void

     * @SuppressWarnings("unused")
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('CREATE SEQUENCE logs.app_issues_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(<<<'SQL'
CREATE TABLE logs.app_issues 
    (
        id INT NOT NULL, 
        subject VARCHAR(255) NOT NULL, 
        description TEXT NOT NULL,
        reporter_name VARCHAR(255) NOT NULL, 
        redmine_task_id INT DEFAULT NULL, 
        PRIMARY KEY(id)
    )
SQL
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
