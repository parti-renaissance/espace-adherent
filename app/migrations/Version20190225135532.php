<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190225135532 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE elections SET introduction = REPLACE(introduction, \'https://www.service-public.fr/particuliers/actualites/A10598\', \'https://www.diplomatie.gouv.fr/fr/services-aux-citoyens/droit-de-vote-et-elections-a-l-etranger/elections-europeennes-2019-mode-d-emploi-pour-les-francais-residant-a-l-62666\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE elections SET introduction = REPLACE(introduction, \'https://www.diplomatie.gouv.fr/fr/services-aux-citoyens/droit-de-vote-et-elections-a-l-etranger/elections-europeennes-2019-mode-d-emploi-pour-les-francais-residant-a-l-62666\', \'https://www.service-public.fr/particuliers/actualites/A10598\')');
    }
}
