<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Class Version20191018104255
 */
final class Version20191018104255 extends AbstractMigration
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

        $this->addSql('ALTER TABLE users ALTER day_start_time_from SET DEFAULT \'08:30\'');
        $this->addSql('ALTER TABLE users ALTER day_start_time_to SET DEFAULT \'08:30\'');
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
