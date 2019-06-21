<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190621160230 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE timeline_manifestos (id BIGINT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE timeline_manifesto_translations (id INT AUTO_INCREMENT NOT NULL, translatable_id BIGINT DEFAULT NULL, title VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, locale VARCHAR(10) NOT NULL, INDEX IDX_F7BD6C172C2AC5D3 (translatable_id), UNIQUE INDEX UNIQ_F7BD6C172C2AC5D34180C698 (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE timeline_manifesto_translations ADD CONSTRAINT FK_F7BD6C172C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_manifestos (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE timeline_measures ADD manifesto_id BIGINT DEFAULT NULL');

        $this->addSql('INSERT INTO timeline_manifestos (id) VALUES (null)');
        $this->addSql(<<<'SQL'
            INSERT INTO timeline_manifesto_translations
            (locale, title, slug, description, translatable_id)
            VALUES (
                'fr',
                'Presidentielle 2017',
                'presidentielle-2017',
                'Programme de la presidentielle 2017',
                (SELECT id FROM timeline_manifestos LIMIT 1)
            )
SQL
        );
        $this->addSql('UPDATE timeline_measures SET manifesto_id = (SELECT id FROM timeline_manifestos LIMIT 1)');

        $this->addSql('ALTER TABLE timeline_measures CHANGE manifesto_id manifesto_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE timeline_measures ADD CONSTRAINT FK_BA475ED737E924 FOREIGN KEY (manifesto_id) REFERENCES timeline_manifestos (id)');
        $this->addSql('CREATE INDEX IDX_BA475ED737E924 ON timeline_measures (manifesto_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE timeline_manifesto_translations DROP FOREIGN KEY FK_F7BD6C172C2AC5D3');
        $this->addSql('ALTER TABLE timeline_measures DROP FOREIGN KEY FK_BA475ED737E924');
        $this->addSql('DROP TABLE timeline_manifestos');
        $this->addSql('DROP TABLE timeline_manifesto_translations');
        $this->addSql('DROP INDEX IDX_BA475ED737E924 ON timeline_measures');
        $this->addSql('ALTER TABLE timeline_measures DROP manifesto_id');
    }
}
