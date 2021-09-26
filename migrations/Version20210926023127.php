<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210926023127 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          jecoute_region
        ADD
          author_id INT UNSIGNED DEFAULT NULL,
        ADD
          administrator_id INT DEFAULT NULL,
        ADD
          enabled TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE
          jecoute_region
        ADD
          CONSTRAINT FK_4E74226FF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_region
        ADD
          CONSTRAINT FK_4E74226F4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_4E74226FF675F31B ON jecoute_region (author_id)');
        $this->addSql('CREATE INDEX IDX_4E74226F4B09E92C ON jecoute_region (administrator_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_region DROP FOREIGN KEY FK_4E74226FF675F31B');
        $this->addSql('ALTER TABLE jecoute_region DROP FOREIGN KEY FK_4E74226F4B09E92C');
        $this->addSql('DROP INDEX IDX_4E74226FF675F31B ON jecoute_region');
        $this->addSql('DROP INDEX IDX_4E74226F4B09E92C ON jecoute_region');
        $this->addSql('ALTER TABLE jecoute_region DROP author_id, DROP administrator_id, DROP enabled');
    }
}
