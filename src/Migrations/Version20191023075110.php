<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\DBALException;

/**
* Class Version20191023075110
*/
final class Version20191023075110 extends AbstractMigration
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

        $this->addSql('CREATE SEQUENCE logs.user_logs_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            <<< SQL
        CREATE TABLE logs.user_logs
            (
                id INT NOT NULL,
                parent_id INT NOT NULL,
                owner_id INT NOT NULL,
                log_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                notice TEXT NOT NULL,
                trigger_element VARCHAR(100) DEFAULT NULL,
                PRIMARY KEY(id)
            )
SQL
        );

        $this->addSql('CREATE INDEX IDX_588974857E3C61F9 ON logs.user_logs (owner_id)');
        $this->addSql('CREATE INDEX idx_user_log_date ON logs.user_logs (log_date)');
        $this->addSql('CREATE INDEX idx_user_log_parent_id ON logs.user_logs (parent_id)');
        $this->addSql(
            <<< SQL
        ALTER TABLE logs.user_logs ADD CONSTRAINT FK_58897485727ACA70
        FOREIGN KEY (parent_id)
        REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
        $this->addSql(
            <<< SQL
        ALTER TABLE logs.user_logs ADD CONSTRAINT FK_588974857E3C61F9
        FOREIGN KEY (owner_id)
        REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
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
        $this->abortIf(true, 'Migration can only be executed safely on \'postgresql\'.');
    }
}
