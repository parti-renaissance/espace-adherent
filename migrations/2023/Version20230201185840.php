<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230201185840 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_charter DROP FOREIGN KEY FK_D6F94F2B25F06C53');
        $this->addSql('ALTER TABLE
          adherent_charter
        ADD
          CONSTRAINT FK_D6F94F2B25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE my_team_delegated_access DROP FOREIGN KEY FK_421C13B98825BEFA');
        $this->addSql('ALTER TABLE
          my_team_delegated_access
        ADD
          CONSTRAINT FK_421C13B98825BEFA FOREIGN KEY (delegator_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_charter DROP FOREIGN KEY FK_D6F94F2B25F06C53');
        $this->addSql('ALTER TABLE
          adherent_charter
        ADD
          CONSTRAINT FK_D6F94F2B25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE my_team_delegated_access DROP FOREIGN KEY FK_421C13B98825BEFA');
        $this->addSql('ALTER TABLE
          my_team_delegated_access
        ADD
          CONSTRAINT FK_421C13B98825BEFA FOREIGN KEY (delegator_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
