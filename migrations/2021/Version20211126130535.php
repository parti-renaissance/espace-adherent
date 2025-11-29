<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211126130535 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE pap_building_event (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          action VARCHAR(25) NOT NULL,
          type VARCHAR(25) NOT NULL,
          identifier VARCHAR(50) NOT NULL,
          building_id INT UNSIGNED DEFAULT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          created_at DATETIME NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_D9F291044D2A7E12 (building_id),
          INDEX IDX_D9F29104F639F774 (campaign_id),
          INDEX IDX_D9F29104F675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          pap_building_event
        ADD
          CONSTRAINT FK_D9F291044D2A7E12 FOREIGN KEY (building_id) REFERENCES pap_building (id)');
        $this->addSql('ALTER TABLE
          pap_building_event
        ADD
          CONSTRAINT FK_D9F29104F639F774 FOREIGN KEY (campaign_id) REFERENCES pap_campaign (id)');
        $this->addSql('ALTER TABLE
          pap_building_event
        ADD
          CONSTRAINT FK_D9F29104F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_building_block_statistics
        ADD
          closed_by_id INT UNSIGNED DEFAULT NULL,
        ADD
          closed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_building_block_statistics
        ADD
          CONSTRAINT FK_8B79BF60E1FA7797 FOREIGN KEY (closed_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_8B79BF60E1FA7797 ON pap_building_block_statistics (closed_by_id)');
        $this->addSql('ALTER TABLE
          pap_building_statistics
        ADD
          closed_by_id INT UNSIGNED DEFAULT NULL,
        ADD
          closed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_building_statistics
        ADD
          CONSTRAINT FK_B6FB4E7BE1FA7797 FOREIGN KEY (closed_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_B6FB4E7BE1FA7797 ON pap_building_statistics (closed_by_id)');
        $this->addSql('ALTER TABLE
          pap_floor_statistics
        ADD
          closed_by_id INT UNSIGNED DEFAULT NULL,
        ADD
          closed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_floor_statistics
        ADD
          CONSTRAINT FK_853B68C8E1FA7797 FOREIGN KEY (closed_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_853B68C8E1FA7797 ON pap_floor_statistics (closed_by_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE pap_building_event');
        $this->addSql('ALTER TABLE pap_building_block_statistics DROP FOREIGN KEY FK_8B79BF60E1FA7797');
        $this->addSql('DROP INDEX IDX_8B79BF60E1FA7797 ON pap_building_block_statistics');
        $this->addSql('ALTER TABLE pap_building_block_statistics DROP closed_by_id, DROP closed_at');
        $this->addSql('ALTER TABLE pap_building_statistics DROP FOREIGN KEY FK_B6FB4E7BE1FA7797');
        $this->addSql('DROP INDEX IDX_B6FB4E7BE1FA7797 ON pap_building_statistics');
        $this->addSql('ALTER TABLE pap_building_statistics DROP closed_by_id, DROP closed_at');
        $this->addSql('ALTER TABLE pap_floor_statistics DROP FOREIGN KEY FK_853B68C8E1FA7797');
        $this->addSql('DROP INDEX IDX_853B68C8E1FA7797 ON pap_floor_statistics');
        $this->addSql('ALTER TABLE pap_floor_statistics DROP closed_by_id, DROP closed_at');
    }
}
