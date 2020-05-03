<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200502224418 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE application_request_volunteer DROP FOREIGN KEY FK_1139657025F06C53');
        $this->addSql('ALTER TABLE 
          application_request_volunteer 
        ADD 
          CONSTRAINT FK_1139657025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE application_request_running_mate DROP FOREIGN KEY FK_D1D6095625F06C53');
        $this->addSql('ALTER TABLE 
          application_request_running_mate 
        ADD 
          CONSTRAINT FK_D1D6095625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE application_request_running_mate DROP FOREIGN KEY FK_D1D6095625F06C53');
        $this->addSql('ALTER TABLE 
          application_request_running_mate 
        ADD 
          CONSTRAINT FK_D1D6095625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE application_request_volunteer DROP FOREIGN KEY FK_1139657025F06C53');
        $this->addSql('ALTER TABLE 
          application_request_volunteer 
        ADD 
          CONSTRAINT FK_1139657025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
    }
}
