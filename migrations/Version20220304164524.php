<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220304164524 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          contact
        ADD
          adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          processed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE
          contact
        ADD
          CONSTRAINT FK_4C62E63825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('CREATE INDEX IDX_4C62E63825F06C53 ON contact (adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E63825F06C53');
        $this->addSql('DROP INDEX IDX_4C62E63825F06C53 ON contact');
        $this->addSql('ALTER TABLE contact DROP adherent_id, DROP processed_at');
    }
}
