<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180108044811 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE timeline_measure_translations (id INT AUTO_INCREMENT NOT NULL, translatable_id BIGINT DEFAULT NULL, title VARCHAR(100) NOT NULL, locale VARCHAR(10) NOT NULL, INDEX IDX_5C9EB6072C2AC5D3 (translatable_id), UNIQUE INDEX UNIQ_5C9EB6072C2AC5D34180C698 (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE timeline_measure_translations ADD CONSTRAINT FK_5C9EB6072C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_measures (id) ON DELETE CASCADE');
        $this->addSQL(<<<'SQL'
            INSERT INTO timeline_measure_translations
            (
                translatable_id,
                locale,
                title
            )
            (
                SELECT
                    measure.id,
                    'fr',
                    measure.title
                FROM timeline_measures measure
            )
SQL
        );
        $this->addSql('ALTER TABLE timeline_measures DROP title');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE timeline_measures ADD title VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql(<<<'SQL'
            UPDATE timeline_measures measure
            INNER JOIN timeline_measure_translations translation
                ON measure.id = translation.translatable_id
                AND translation.locale = 'fr'
            SET measure.title = translation.title
SQL
        );
        $this->addSql('DROP TABLE timeline_measure_translations');
    }
}
