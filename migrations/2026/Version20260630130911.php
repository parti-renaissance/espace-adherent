<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630130911 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  poll
                ADD
                  start_at DATETIME DEFAULT NULL,
                ADD
                  result_display_end_at DATETIME DEFAULT NULL,
                ADD
                  description LONGTEXT DEFAULT NULL,
                ADD
                  participant_count_threshold SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
                ADD
                  result_display_mode VARCHAR(32) DEFAULT 'after_vote' NOT NULL
            SQL);
        $this->addSql('UPDATE poll SET start_at = created_at WHERE start_at IS NULL');
        $this->addSql('UPDATE poll SET result_display_end_at = finish_at WHERE result_display_end_at IS NULL');
        $this->addSql('ALTER TABLE poll CHANGE start_at start_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE poll ADD created_by_administrator_id INT DEFAULT NULL, ADD updated_by_administrator_id INT DEFAULT NULL');
        $this->addSql('UPDATE poll SET created_by_administrator_id = administrator_id');
        $this->addSql('ALTER TABLE poll ADD CONSTRAINT FK_84BCFA459DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE poll ADD CONSTRAINT FK_84BCFA45CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_84BCFA459DF5350C ON poll (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_84BCFA45CF1918FF ON poll (updated_by_administrator_id)');
        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY `FK_84BCFA454B09E92C`');
        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY `FK_84BCFA459F2C3FAB`');
        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY `FK_84BCFA45F675F31B`');
        $this->addSql('DROP INDEX IDX_84BCFA454B09E92C ON poll');
        $this->addSql('DROP INDEX IDX_84BCFA459F2C3FAB ON poll');
        $this->addSql('DROP INDEX IDX_84BCFA45F675F31B ON poll');
        $this->addSql('ALTER TABLE poll DROP administrator_id, DROP zone_id, DROP author_id, DROP type');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE poll ADD administrator_id INT DEFAULT NULL, ADD zone_id INT UNSIGNED DEFAULT NULL, ADD author_id INT UNSIGNED DEFAULT NULL, ADD type VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE poll SET administrator_id = created_by_administrator_id');
        $this->addSql('ALTER TABLE poll ADD CONSTRAINT `FK_84BCFA454B09E92C` FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE poll ADD CONSTRAINT `FK_84BCFA459F2C3FAB` FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE poll ADD CONSTRAINT `FK_84BCFA45F675F31B` FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_84BCFA454B09E92C ON poll (administrator_id)');
        $this->addSql('CREATE INDEX IDX_84BCFA459F2C3FAB ON poll (zone_id)');
        $this->addSql('CREATE INDEX IDX_84BCFA45F675F31B ON poll (author_id)');
        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY FK_84BCFA459DF5350C');
        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY FK_84BCFA45CF1918FF');
        $this->addSql('DROP INDEX IDX_84BCFA459DF5350C ON poll');
        $this->addSql('DROP INDEX IDX_84BCFA45CF1918FF ON poll');
        $this->addSql('ALTER TABLE poll DROP created_by_administrator_id, DROP updated_by_administrator_id');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  poll
                DROP
                  start_at,
                DROP
                  result_display_end_at,
                DROP
                  description,
                DROP
                  participant_count_threshold,
                DROP
                  result_display_mode
            SQL);
    }
}
