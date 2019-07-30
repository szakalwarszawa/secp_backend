<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190603202747 extends AbstractMigration
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
     * @return void
     * @throws DBALException
     * @SuppressWarnings("unused")
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('CREATE SEQUENCE "sections_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "departments_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            'CREATE TABLE "sections" (id INT NOT NULL, department_id INT NOT NULL, name VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX idx_sections_name ON "sections" (name)');
        $this->addSql('CREATE INDEX idx_sections_active ON "sections" (active)');
        $this->addSql('CREATE INDEX idx_sections_department_id ON "sections" (department_id)');
        $this->addSql(
            'CREATE TABLE section_managers (section_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(section_id, user_id))'
        );
        $this->addSql('CREATE INDEX IDX_3924BA5AD823E37A ON section_managers (section_id)');
        $this->addSql('CREATE INDEX IDX_3924BA5AA76ED395 ON section_managers (user_id)');
        $this->addSql(
            'CREATE TABLE "departments" (id INT NOT NULL, name VARCHAR(255) NOT NULL, short_name VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX idx_departments_name ON "departments" (name)');
        $this->addSql('CREATE INDEX idx_departments_short_name ON "departments" (short_name)');
        $this->addSql('CREATE INDEX idx_departments_active ON "departments" (active)');
        $this->addSql(
            'CREATE TABLE department_managers (department_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(department_id, user_id))'
        );
        $this->addSql('CREATE INDEX IDX_90F7FA2CAE80F5DF ON department_managers (department_id)');
        $this->addSql('CREATE INDEX IDX_90F7FA2CA76ED395 ON department_managers (user_id)');
        $this->addSql(
            'ALTER TABLE "sections" ADD CONSTRAINT FK_2B964398AE80F5DF FOREIGN KEY (department_id) REFERENCES "departments" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE section_managers ADD CONSTRAINT FK_3924BA5AD823E37A FOREIGN KEY (section_id) REFERENCES "sections" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE section_managers ADD CONSTRAINT FK_3924BA5AA76ED395 FOREIGN KEY (user_id) REFERENCES "users" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE department_managers ADD CONSTRAINT FK_90F7FA2CAE80F5DF FOREIGN KEY (department_id) REFERENCES "departments" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE department_managers ADD CONSTRAINT FK_90F7FA2CA76ED395 FOREIGN KEY (user_id) REFERENCES "users" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql('ALTER TABLE users ADD department_id INT NOT NULL');
        $this->addSql('ALTER TABLE users ADD section_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD sam_account_name VARCHAR(256) NOT NULL');
        $this->addSql('ALTER TABLE users ADD username VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE users ADD first_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE users ADD last_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE users ADD distinguished_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ALTER roles TYPE TEXT');
        $this->addSql('ALTER TABLE users ALTER roles DROP DEFAULT');
        $this->addSql('ALTER TABLE users ALTER password TYPE VARCHAR(256)');
        $this->addSql('COMMENT ON COLUMN users.roles IS \'(DC2Type:simple_array)\'');
        $this->addSql(
            'ALTER TABLE users ADD CONSTRAINT FK_1483A5E9AE80F5DF FOREIGN KEY (department_id) REFERENCES "departments" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D823E37A FOREIGN KEY (section_id) REFERENCES "sections" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql('CREATE INDEX idx_users_username ON users (username)');
        $this->addSql('CREATE INDEX idx_users_sam_account_name ON users (sam_account_name)');
        $this->addSql('CREATE INDEX idx_users_email ON users (email)');
        $this->addSql('CREATE INDEX idx_users_last_name ON users (last_name)');
        $this->addSql('CREATE INDEX idx_users_first_name ON users (first_name)');
        $this->addSql('CREATE INDEX idx_users_department_id ON users (department_id)');
        $this->addSql('CREATE INDEX idx_users_section_id ON users (section_id)');
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
