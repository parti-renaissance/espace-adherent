<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220705022722 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          donations
        ADD
          adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          source VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          donations
        ADD
          CONSTRAINT FK_CDE9896225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_CDE9896225F06C53 ON donations (adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations DROP FOREIGN KEY FK_CDE9896225F06C53');
        $this->addSql('DROP INDEX IDX_CDE9896225F06C53 ON donations');
        $this->addSql('ALTER TABLE donations DROP adherent_id, DROP source');
    }
}
