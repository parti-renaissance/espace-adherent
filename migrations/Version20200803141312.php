<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200803141312 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voting_platform_candidate ADD adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          voting_platform_candidate 
        ADD 
          CONSTRAINT FK_3F426D6D25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_3F426D6D25F06C53 ON voting_platform_candidate (adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voting_platform_candidate DROP FOREIGN KEY FK_3F426D6D25F06C53');
        $this->addSql('DROP INDEX IDX_3F426D6D25F06C53 ON voting_platform_candidate');
        $this->addSql('ALTER TABLE voting_platform_candidate DROP adherent_id');
    }
}
