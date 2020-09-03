<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200903170555 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP FOREIGN KEY FK_39885B6A35D7AF0');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy 
        ADD 
          CONSTRAINT FK_39885B6A35D7AF0 FOREIGN KEY (invitation_id) REFERENCES territorial_council_candidacy_invitation (id) ON DELETE 
        SET 
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP FOREIGN KEY FK_39885B6A35D7AF0');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy 
        ADD 
          CONSTRAINT FK_39885B6A35D7AF0 FOREIGN KEY (invitation_id) REFERENCES territorial_council_candidacy_invitation (id) ON DELETE CASCADE');
    }
}
