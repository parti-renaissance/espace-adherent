<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220321165335 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A876C4DDA');
        $this->addSql('ALTER TABLE
          events
        ADD
          CONSTRAINT FK_5387574A876C4DDA FOREIGN KEY (organizer_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A876C4DDA');
        $this->addSql('ALTER TABLE
          events
        ADD
          CONSTRAINT FK_5387574A876C4DDA FOREIGN KEY (organizer_id) REFERENCES adherents (id) ON UPDATE NO ACTION');
    }
}
