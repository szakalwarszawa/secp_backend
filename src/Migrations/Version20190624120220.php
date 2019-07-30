<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190624120220 extends AbstractMigration
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

        $this->addSql('CREATE SEQUENCE "absence_types_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "presence_types_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            'CREATE TABLE "absence_types" (id INT NOT NULL, short_name VARCHAR(10) NOT NULL, name VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX idx_absence_types_short_name ON "absence_types" (short_name)');
        $this->addSql('CREATE INDEX idx_absence_types_name ON "absence_types" (active, name)');
        $this->addSql('CREATE INDEX idx_absence_types_active ON "absence_types" (active)');
        $this->addSql(
            'CREATE TABLE "presence_types" (id INT NOT NULL, short_name VARCHAR(10) NOT NULL, name VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX idx_presence_types_short_name ON "presence_types" (short_name)');
        $this->addSql('CREATE INDEX idx_presence_types_name ON "presence_types" (active, name)');
        $this->addSql('CREATE INDEX idx_presence_types_active ON "presence_types" (active)');

        $this->addSql(
            <<<SQL
INSERT INTO "absence_types" ("id", "short_name", "name", "active") VALUES 
(1, 'UW', 'urlop wypoczynkowy', true),
(2, 'UR', 'dodatkowy urlop dla niepełnosprawnego', true),
(3, 'UŻ', 'urlop na żądanie', true),
(4, 'UO', 'urlop okolicznościowy', true),
(5, 'UB', 'urlop bezpłatny', true),
(6, 'OP', 'opieka nad dzieckiem z art. 188 KP', true),
(7, 'K', 'krwiodawstwo', true),
(8, 'D', 'delegacja', true),
(9, 'USZ', 'urlop szkoleniowy', true),
(10, 'WS', ' wezwanie do sądu/policji/prokuratury', true),
(11, 'ZP', 'zwolnienie na poszukiwanie pracy', true),
(12, 'ZW', 'zwolnienie z obowiązku świadczenia pracy', true),
(13, 'UM', 'urlop macierzyński', true),
(14, 'UR', 'urlop rodzicielski', true),
(15, 'UM/UR ', 'urlop rodzicielski łączony z pracą', true),
(16, 'UOC', 'urlop ojcowski', true),
(17, 'WYCH', 'urlop wychowawczy', true),
(18, 'ZL', 'zwolnienie lekarskie pracownik', true),
(19, 'ZL OP', 'zwolnienie lekarskie na chore dziecko/członka rodziny', true),
(20, 'OP Z', 'opieka nad zdrowym dzieckiem', true),
(21, 'ŚR', 'świadczenie rehabilitacyjne', true),
(22, 'NU', 'nieobecność usprawiedliwiona płatna', true),
(23, 'NP.', 'nieobecność usprawiedliwiona niepłatna', true),
(24, 'NN', 'nieobecność nieusprawiedliwiona', true),
(25, 'W5', 'dzień wolny z tytułu 5-dniowego tygodnia pracy', true),
(26, 'WN', 'niedziela wolna od pracy/ dzień wolny za pracę w niedzielę', true),
(27, 'WR', 'dzień wolny z harmonogramu', true),
(28, 'WŚ', 'dzień wolny z tytułu święta', true);
SQL
        );

        $this->addSql(
            <<<SQL
INSERT INTO "presence_types" ("id", "short_name", "name", "active") VALUES 
(1, 'O', 'obecność', true),
(2, 'HO', 'home office', true),
(3, 'S', 'szkolenie', true),
(4, 'D', 'delegację', true),
(5, 'N', 'nieobecność', true),
(6, 'DD', 'dyżur domowy', true),
(7, 'DP', 'dyżur w pracy', true);
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
