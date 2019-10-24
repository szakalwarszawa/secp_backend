<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190925071006 extends AbstractMigration
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

        $this->addSql('ALTER TABLE dictionary.user_work_schedule_statuses ADD rules JSON DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN dictionary.user_work_schedule_statuses.rules IS \'(DC2Type:json_array)\'');
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
