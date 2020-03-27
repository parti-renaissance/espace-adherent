<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200330100733 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE vote_result_list SET nuance = \'PRG\' WHERE nuance = \'RDG\'');
        $this->addSql('UPDATE ministry_list_total_result SET nuance = \'PRG\' WHERE nuance = \'RDG\'');
        $this->addSql('UPDATE elected_representative_mandate SET political_affiliation = \'PRG\' WHERE political_affiliation = \'RDG\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE vote_result_list SET nuance = \'RDG\' WHERE nuance = \'PRG\'');
        $this->addSql('UPDATE ministry_list_total_result SET nuance = \'RDG\' WHERE nuance = \'PRG\'');
        $this->addSql('UPDATE elected_representative_mandate SET political_affiliation = \'RDG\' WHERE political_affiliation = \'PRG\'');
    }
}
