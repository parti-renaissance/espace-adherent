<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190605235511 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          registered_since DATE DEFAULT NULL, 
        ADD 
          registered_until DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP registered_since, DROP registered_until');
    }
}
