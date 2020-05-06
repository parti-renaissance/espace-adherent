<?php

namespace Migrations;

use App\Entity\AdherentTagEnum;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190118142342 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO adherent_tags (`name`) VALUES(?)',
            [AdherentTagEnum::LAREM],
            [\PDO::PARAM_STR]
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'DELETE FROM adherent_tags WHERE `name` = ?',
            [AdherentTagEnum::LAREM],
            [\PDO::PARAM_STR]
        );
    }
}
