<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190613095719 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE running_mate_request_application_request_tag (
          running_mate_request_id INT UNSIGNED NOT NULL, 
          application_request_tag_id INT NOT NULL, 
          INDEX IDX_9D534FCFCEDF4387 (running_mate_request_id), 
          INDEX IDX_9D534FCF9644FEDA (application_request_tag_id), 
          PRIMARY KEY(
            running_mate_request_id, application_request_tag_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE volunteer_request_application_request_tag (
          volunteer_request_id INT UNSIGNED NOT NULL, 
          application_request_tag_id INT NOT NULL, 
          INDEX IDX_6F3FA269B8D6887 (volunteer_request_id), 
          INDEX IDX_6F3FA2699644FEDA (application_request_tag_id), 
          PRIMARY KEY(
            volunteer_request_id, application_request_tag_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE application_request_tag (
          id INT AUTO_INCREMENT NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          running_mate_request_application_request_tag 
        ADD 
          CONSTRAINT FK_9D534FCFCEDF4387 FOREIGN KEY (running_mate_request_id) REFERENCES application_request_running_mate (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          running_mate_request_application_request_tag 
        ADD 
          CONSTRAINT FK_9D534FCF9644FEDA FOREIGN KEY (application_request_tag_id) REFERENCES application_request_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          volunteer_request_application_request_tag 
        ADD 
          CONSTRAINT FK_6F3FA269B8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          volunteer_request_application_request_tag 
        ADD 
          CONSTRAINT FK_6F3FA2699644FEDA FOREIGN KEY (application_request_tag_id) REFERENCES application_request_tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE running_mate_request_application_request_tag DROP FOREIGN KEY FK_9D534FCF9644FEDA');
        $this->addSql('ALTER TABLE volunteer_request_application_request_tag DROP FOREIGN KEY FK_6F3FA2699644FEDA');
        $this->addSql('DROP TABLE running_mate_request_application_request_tag');
        $this->addSql('DROP TABLE volunteer_request_application_request_tag');
        $this->addSql('DROP TABLE application_request_tag');
    }
}
