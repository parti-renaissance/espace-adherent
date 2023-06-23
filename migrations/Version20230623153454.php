<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230623153454 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE campus_registration DROP FOREIGN KEY FK_30249D7B25F06C53');
        $this->addSql('ALTER TABLE
          campus_registration
        ADD
          CONSTRAINT FK_30249D7B25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE campus_registration DROP FOREIGN KEY FK_30249D7B25F06C53');
        $this->addSql('ALTER TABLE
          campus_registration
        ADD
          CONSTRAINT FK_30249D7B25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
