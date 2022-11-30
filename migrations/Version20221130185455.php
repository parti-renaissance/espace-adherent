<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221130185455 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation ADD is_blank_vote_enabled TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE voting_platform_candidate ADD position SMALLINT UNSIGNED DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation DROP is_blank_vote_enabled');
        $this->addSql('ALTER TABLE voting_platform_candidate DROP position');
    }
}
