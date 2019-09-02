<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190829073452 extends AbstractMigration
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
     * @throws DBALException
     * @SuppressWarnings("unused")
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE presence_types ADD working_day_restriction INT DEFAULT 0 NOT NULL');
    }

    /**
     * @param Schema $schema
     *
     * @SuppressWarnings("unused")
     *
     * @return void
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf(true, 'Downgrade migration can only be executed by next migration.');
    }
}
