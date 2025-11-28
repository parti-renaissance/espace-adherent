<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211220162832 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_campaign_history DROP FOREIGN KEY FK_5A3F26F725F06C53');
        $this->addSql('DROP INDEX IDX_5A3F26F725F06C53 ON pap_campaign_history');
        $this->addSql('ALTER TABLE pap_campaign_history DROP adherent_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_campaign_history ADD adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_5A3F26F725F06C53 ON pap_campaign_history (adherent_id)');
    }
}
