<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190926171344 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_segment (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          author_id INT UNSIGNED NOT NULL, 
          label VARCHAR(255) NOT NULL, 
          member_ids LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', 
          mailchimp_id VARCHAR(255) DEFAULT NULL, 
          synchronized TINYINT(1) DEFAULT \'0\' NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          INDEX IDX_9DF0C7EBF675F31B (author_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          adherent_segment 
        ADD 
          CONSTRAINT FK_9DF0C7EBF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE adherent_message_filters ADD adherent_segment_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          CONSTRAINT FK_28CA9F94FAF04979 FOREIGN KEY (adherent_segment_id) REFERENCES adherent_segment (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_28CA9F94FAF04979 ON adherent_message_filters (adherent_segment_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94FAF04979');
        $this->addSql('DROP TABLE adherent_segment');
        $this->addSql('DROP INDEX IDX_28CA9F94FAF04979 ON adherent_message_filters');
        $this->addSql('ALTER TABLE adherent_message_filters DROP adherent_segment_id');
    }
}
