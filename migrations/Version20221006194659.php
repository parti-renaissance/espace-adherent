<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221006194659 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_request
        ADD
          adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherent_request
        ADD
          CONSTRAINT FK_BEE6BD1125F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_BEE6BD1125F06C53 ON adherent_request (adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_request DROP FOREIGN KEY FK_BEE6BD1125F06C53');
        $this->addSql('DROP INDEX IDX_BEE6BD1125F06C53 ON adherent_request');
        $this->addSql('ALTER TABLE adherent_request DROP adherent_id');
    }
}
