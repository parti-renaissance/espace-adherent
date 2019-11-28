<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191128155145 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD managed_district_id INT UNSIGNED DEFAULT NULL');

        $this->addSql(
            'UPDATE adherents AS a
            INNER JOIN districts AS d ON d.adherent_id = a.id
            SET a.managed_district_id = d.id'
        );

        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3A132C3C5 FOREIGN KEY (managed_district_id) REFERENCES districts (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3A132C3C5 ON adherents (managed_district_id)');
        $this->addSql('ALTER TABLE districts DROP FOREIGN KEY FK_68E318DC25F06C53');
        $this->addSql('DROP INDEX UNIQ_68E318DC25F06C53 ON districts');
        $this->addSql('ALTER TABLE districts DROP adherent_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3A132C3C5');
        $this->addSql('DROP INDEX UNIQ_562C7DA3A132C3C5 ON adherents');

        $this->addSql('ALTER TABLE districts ADD adherent_id INT UNSIGNED DEFAULT NULL');

        $this->addSql(
            'UPDATE districts AS d
            INNER JOIN adherents AS a ON a.managed_district_id = d.id
            SET d.adherent_id = a.id'
        );

        $this->addSql('ALTER TABLE adherents DROP managed_district_id');

        $this->addSql('ALTER TABLE 
          districts 
        ADD 
          CONSTRAINT FK_68E318DC25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_68E318DC25F06C53 ON districts (adherent_id)');
    }
}
