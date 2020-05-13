<?php

namespace Migrations;

use App\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200423124633 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent_person_link ADD co_referent VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE referent_team_member ADD limited TINYINT(1) DEFAULT \'0\' NOT NULL');

        $this->addSql(
            'UPDATE referent_person_link SET co_referent = :co_referent WHERE is_co_referent = 1',
            ['co_referent' => ReferentPersonLink::CO_REFERENT]
        );

        $this->addSql('ALTER TABLE referent_person_link DROP is_co_referent');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent_person_link ADD is_co_referent TINYINT(1) DEFAULT \'0\' NOT NULL, DROP co_referent');
        $this->addSql('ALTER TABLE referent_team_member DROP limited');
    }
}
