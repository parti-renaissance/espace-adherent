<?php

namespace Migrations;

use AppBundle\Entity\AdherentTagEnum;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190118142342 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(sprintf("INSERT INTO adherent_tags (`name`) VALUES('%s')", AdherentTagEnum::LAREM));
    }

    public function down(Schema $schema): void
    {
        $this->addSql(sprintf("DELETE FROM adherent_tags WHERE label = '%s'", AdherentTagEnum::LAREM));
    }
}
