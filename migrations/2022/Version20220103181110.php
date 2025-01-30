<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220103181110 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_survey DROP FOREIGN KEY FK_EC4948E54B09E92C');
        $this->addSql('ALTER TABLE jecoute_survey DROP FOREIGN KEY FK_EC4948E5F675F31B');
        $this->addSql('DROP INDEX IDX_EC4948E54B09E92C ON jecoute_survey');
        $this->addSql('DROP INDEX IDX_EC4948E5F675F31B ON jecoute_survey');
        $this->addSql('ALTER TABLE
          jecoute_survey
        ADD
          updated_by_administrator_id INT DEFAULT NULL,
        ADD
          updated_by_adherent_id INT UNSIGNED DEFAULT NULL,
        CHANGE
          administrator_id created_by_administrator_id INT DEFAULT NULL,
        CHANGE
          author_id created_by_adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          jecoute_survey
        ADD
          CONSTRAINT FK_EC4948E59DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_survey
        ADD
          CONSTRAINT FK_EC4948E5CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_survey
        ADD
          CONSTRAINT FK_EC4948E585C9D733 FOREIGN KEY (created_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_survey
        ADD
          CONSTRAINT FK_EC4948E5DF6CFDC9 FOREIGN KEY (updated_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_EC4948E59DF5350C ON jecoute_survey (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_EC4948E5CF1918FF ON jecoute_survey (updated_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_EC4948E585C9D733 ON jecoute_survey (created_by_adherent_id)');
        $this->addSql('CREATE INDEX IDX_EC4948E5DF6CFDC9 ON jecoute_survey (updated_by_adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_survey DROP FOREIGN KEY FK_EC4948E59DF5350C');
        $this->addSql('ALTER TABLE jecoute_survey DROP FOREIGN KEY FK_EC4948E5CF1918FF');
        $this->addSql('ALTER TABLE jecoute_survey DROP FOREIGN KEY FK_EC4948E585C9D733');
        $this->addSql('ALTER TABLE jecoute_survey DROP FOREIGN KEY FK_EC4948E5DF6CFDC9');
        $this->addSql('DROP INDEX IDX_EC4948E59DF5350C ON jecoute_survey');
        $this->addSql('DROP INDEX IDX_EC4948E5CF1918FF ON jecoute_survey');
        $this->addSql('DROP INDEX IDX_EC4948E585C9D733 ON jecoute_survey');
        $this->addSql('DROP INDEX IDX_EC4948E5DF6CFDC9 ON jecoute_survey');
        $this->addSql('ALTER TABLE
          jecoute_survey
        ADD
          author_id INT UNSIGNED DEFAULT NULL,
        ADD
          administrator_id INT DEFAULT NULL,
        DROP
          created_by_administrator_id,
        DROP
          updated_by_administrator_id,
        DROP
          created_by_adherent_id,
        DROP
          updated_by_adherent_id');
        $this->addSql('ALTER TABLE
          jecoute_survey
        ADD
          CONSTRAINT FK_EC4948E54B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_survey
        ADD
          CONSTRAINT FK_EC4948E5F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_EC4948E54B09E92C ON jecoute_survey (administrator_id)');
        $this->addSql('CREATE INDEX IDX_EC4948E5F675F31B ON jecoute_survey (author_id)');
    }
}
