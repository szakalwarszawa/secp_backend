<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190716100351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('ALTER TABLE presence_types ADD is_absence BOOLEAN DEFAULT FALSE NOT NULL');
        $this->addSql('ALTER TABLE presence_types ADD is_timed BOOLEAN DEFAULT TRUE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(true, 'Downgrade migration can only be executed by next migration.');
    }
}
