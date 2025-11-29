<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211209142923 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE phoning_campaign_history DROP FOREIGN KEY FK_EC19119825F06C53');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC19119825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE phoning_campaign_history DROP FOREIGN KEY FK_EC19119825F06C53');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC19119825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
