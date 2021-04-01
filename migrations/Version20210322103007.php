<?php

namespace Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210322103007 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY FK_84BCFA45B03A8386');
        $this->addSql('DROP INDEX IDX_84BCFA45B03A8386 ON poll');
        $this->addSql('ALTER TABLE 
          poll 
        ADD 
          author_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          zone_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          published TINYINT(1) DEFAULT \'0\' NOT NULL, 
        ADD 
          type VARCHAR(255) DEFAULT \'national\', 
        CHANGE created_by_id administrator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          poll 
        ADD 
          CONSTRAINT FK_84BCFA45F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('ALTER TABLE 
          poll 
        ADD 
          CONSTRAINT FK_84BCFA459F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE 
          poll 
        ADD 
          CONSTRAINT FK_84BCFA454B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_84BCFA45F675F31B ON poll (author_id)');
        $this->addSql('CREATE INDEX IDX_84BCFA459F2C3FAB ON poll (zone_id)');
        $this->addSql('CREATE INDEX IDX_84BCFA454B09E92C ON poll (administrator_id)');
    }

    public function postUp(Schema $schema): void
    {
        /** @var Connection $connection */
        $this->connection->executeUpdate('UPDATE poll SET published = 1');
        $this->connection->executeQuery('ALTER TABLE poll CHANGE type type VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY FK_84BCFA45F675F31B');
        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY FK_84BCFA459F2C3FAB');
        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY FK_84BCFA454B09E92C');
        $this->addSql('DROP INDEX IDX_84BCFA45F675F31B ON poll');
        $this->addSql('DROP INDEX IDX_84BCFA459F2C3FAB ON poll');
        $this->addSql('DROP INDEX IDX_84BCFA454B09E92C ON poll');
        $this->addSql('ALTER TABLE 
          poll 
        DROP 
          author_id, 
        DROP 
          zone_id, 
        DROP 
          published, 
        DROP 
          type, 
          CHANGE administrator_id created_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          poll 
        ADD 
          CONSTRAINT FK_84BCFA45B03A8386 FOREIGN KEY (created_by_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_84BCFA45B03A8386 ON poll (created_by_id)');
    }
}
