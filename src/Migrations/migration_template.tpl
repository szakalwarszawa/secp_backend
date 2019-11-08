<?php

declare(strict_types=1);

namespace <namespace>;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\DBALException;

/**
* Class Version<version>
*/
final class Version<version> extends AbstractMigration
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
<up>
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
        $this->abortIf(true, 'Downgrade migration can only be executed by next migration.');
    }
}
