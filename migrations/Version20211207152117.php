<?php

namespace Migrations;

use App\Scope\ScopeVisibilityEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211207152117 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team ADD zone_id INT UNSIGNED DEFAULT NULL, ADD visibility VARCHAR(30) DEFAULT NULL');
        $this->addSql('UPDATE team SET visibility = :visibility', ['visibility' => ScopeVisibilityEnum::NATIONAL]);
        $this->addSql('ALTER TABLE team CHANGE visibility visibility VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE
          team
        ADD
          CONSTRAINT FK_C4E0A61F9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_C4E0A61F9F2C3FAB ON team (zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F9F2C3FAB');
        $this->addSql('DROP INDEX IDX_C4E0A61F9F2C3FAB ON team');
        $this->addSql('ALTER TABLE team DROP zone_id, DROP visibility');
    }
}
