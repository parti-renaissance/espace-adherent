<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240311084338 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event_inscription DROP FOREIGN KEY FK_C332555725F06C53');
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          CONSTRAINT FK_C332555725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event_inscription DROP FOREIGN KEY FK_C332555725F06C53');
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          CONSTRAINT FK_C332555725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
