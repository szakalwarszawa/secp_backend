<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\User;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\DBALException;

/**
* Class Version20191029080949
*/
final class Version20191029080949 extends AbstractMigration
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

        $randomPassword = bin2hex(openssl_random_pseudo_bytes(100));
        $systemUsername = User::SYSTEM_USERNAME;
        $this->addSql(<<<'SQL'
INSERT INTO public.departments (id, name, short_name, active) 
VALUES (
        0,
        'System', 
        'SYSTEM', 
        true
        )
SQL
        );
        $this->addSql(<<<SQL
INSERT INTO public.users 
    (
     id, 
     email, 
     roles, 
     password,
     department_id, 
     section_id, 
     sam_account_name, 
     username, 
     first_name, 
     last_name, 
     default_work_schedule_profile_id
     )
VALUES (
        0, 
        'system@system',
        'ROLE_ADMIN', 
        '{$randomPassword}',
        (SELECT id from public.departments d WHERE d.name='System'),
        null, 
        '{$systemUsername}',
        '{$systemUsername}',
        '{$systemUsername}',
        '{$systemUsername}',
        (SELECT id from dictionary.work_schedule_profiles p WHERE p.name='Domyślny')
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
        $this->abortIf(true, 'Migration can only be executed safely on \'postgresql\'.');
    }
}
