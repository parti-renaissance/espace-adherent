<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230207011026 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          committees
        ADD
          created_by_adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          updated_by_adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          version SMALLINT UNSIGNED DEFAULT 2 NOT NULL,
        CHANGE
          address_postal_code address_postal_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          committees
        ADD
          CONSTRAINT FK_A36198C685C9D733 FOREIGN KEY (created_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          committees
        ADD
          CONSTRAINT FK_A36198C6DF6CFDC9 FOREIGN KEY (updated_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_A36198C685C9D733 ON committees (created_by_adherent_id)');
        $this->addSql('CREATE INDEX IDX_A36198C6DF6CFDC9 ON committees (updated_by_adherent_id)');
        $this->addSql('CREATE INDEX IDX_A36198C6BF1CD3C3 ON committees (version)');
        $this->addSql('ALTER TABLE committees RENAME INDEX committee_status_idx TO IDX_A36198C67B00651C');
        $this->addSql('ALTER TABLE committees RENAME INDEX committee_canonical_name_unique TO UNIQ_A36198C6674D812');
        $this->addSql('ALTER TABLE committees RENAME INDEX committee_slug_unique TO UNIQ_A36198C6989D9B62');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE committees SET version = 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE committees DROP FOREIGN KEY FK_A36198C685C9D733');
        $this->addSql('ALTER TABLE committees DROP FOREIGN KEY FK_A36198C6DF6CFDC9');
        $this->addSql('DROP INDEX IDX_A36198C685C9D733 ON committees');
        $this->addSql('DROP INDEX IDX_A36198C6DF6CFDC9 ON committees');
        $this->addSql('DROP INDEX IDX_A36198C6BF1CD3C3 ON committees');
        $this->addSql('ALTER TABLE
          committees
        DROP
          created_by_adherent_id,
        DROP
          updated_by_adherent_id,
        DROP
          version,
        CHANGE
          address_postal_code address_postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE committees RENAME INDEX uniq_a36198c6674d812 TO committee_canonical_name_unique');
        $this->addSql('ALTER TABLE committees RENAME INDEX uniq_a36198c6989d9b62 TO committee_slug_unique');
        $this->addSql('ALTER TABLE committees RENAME INDEX idx_a36198c67b00651c TO committee_status_idx');
    }
}
