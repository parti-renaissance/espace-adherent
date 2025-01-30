<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230217143246 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          designation
        ADD
          created_by_adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          updated_by_adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          election_entity_identifier CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE
          designation
        ADD
          CONSTRAINT FK_8947610D85C9D733 FOREIGN KEY (created_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          designation
        ADD
          CONSTRAINT FK_8947610DDF6CFDC9 FOREIGN KEY (updated_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_8947610D85C9D733 ON designation (created_by_adherent_id)');
        $this->addSql('CREATE INDEX IDX_8947610DDF6CFDC9 ON designation (updated_by_adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation DROP FOREIGN KEY FK_8947610D85C9D733');
        $this->addSql('ALTER TABLE designation DROP FOREIGN KEY FK_8947610DDF6CFDC9');
        $this->addSql('DROP INDEX IDX_8947610D85C9D733 ON designation');
        $this->addSql('DROP INDEX IDX_8947610DDF6CFDC9 ON designation');
        $this->addSql('ALTER TABLE
          designation
        DROP
          created_by_adherent_id,
        DROP
          updated_by_adherent_id,
        DROP
          election_entity_identifier');
    }
}
