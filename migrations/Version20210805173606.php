<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210805173606 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE audience_segment (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          filter_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED NOT NULL,
          recipient_count INT UNSIGNED DEFAULT NULL,
          synchronized TINYINT(1) DEFAULT \'0\' NOT NULL,
          mailchimp_id INT DEFAULT NULL,
          UNIQUE INDEX UNIQ_C5C2F52FD395B25E (filter_id),
          INDEX IDX_C5C2F52FF675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          audience_segment
        ADD
          CONSTRAINT FK_C5C2F52FD395B25E FOREIGN KEY (filter_id) REFERENCES adherent_message_filters (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          audience_segment
        ADD
          CONSTRAINT FK_C5C2F52FF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE adherent_message_filters ADD is_certified TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE audience_segment');
        $this->addSql('ALTER TABLE adherent_message_filters DROP is_certified');
    }
}
