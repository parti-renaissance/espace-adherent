<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190531145158 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE running_mate_request_referent_tag (
          running_mate_request_id INT UNSIGNED NOT NULL, 
          referent_tag_id INT UNSIGNED NOT NULL, 
          INDEX IDX_53AB4FABCEDF4387 (running_mate_request_id), 
          INDEX IDX_53AB4FAB9C262DB3 (referent_tag_id), 
          PRIMARY KEY(
            running_mate_request_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE volunteer_request_referent_tag (
          volunteer_request_id INT UNSIGNED NOT NULL, 
          referent_tag_id INT UNSIGNED NOT NULL, 
          INDEX IDX_DA291742B8D6887 (volunteer_request_id), 
          INDEX IDX_DA2917429C262DB3 (referent_tag_id), 
          PRIMARY KEY(
            volunteer_request_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          running_mate_request_referent_tag 
        ADD 
          CONSTRAINT FK_53AB4FABCEDF4387 FOREIGN KEY (running_mate_request_id) REFERENCES application_request_running_mate (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          running_mate_request_referent_tag 
        ADD 
          CONSTRAINT FK_53AB4FAB9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          volunteer_request_referent_tag 
        ADD 
          CONSTRAINT FK_DA291742B8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          volunteer_request_referent_tag 
        ADD 
          CONSTRAINT FK_DA2917429C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE running_mate_request_referent_tag');
        $this->addSql('DROP TABLE volunteer_request_referent_tag');
    }
}
