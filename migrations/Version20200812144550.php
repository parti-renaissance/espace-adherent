<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200812144550 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP FOREIGN KEY FK_39885B68D4924C4');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy 
        ADD 
          CONSTRAINT FK_39885B68D4924C4 FOREIGN KEY (binome_id) REFERENCES territorial_council_candidacy (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP FOREIGN KEY FK_39885B68D4924C4');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy 
        ADD 
          CONSTRAINT FK_39885B68D4924C4 FOREIGN KEY (binome_id) REFERENCES territorial_council_candidacy (id)');
    }
}
