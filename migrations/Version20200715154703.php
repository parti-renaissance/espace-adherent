<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200715154703 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative DROP official_id, DROP is_adherent');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          elected_representative 
        ADD 
          official_id BIGINT DEFAULT NULL, 
        ADD 
          is_adherent TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('UPDATE elected_representative SET is_adherent = IF(adherent_id IS NULL, 0, 1)');
    }
}
