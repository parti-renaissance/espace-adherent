<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200921110741 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE territorial_council_feed_item (id INT UNSIGNED AUTO_INCREMENT NOT NULL, territorial_council_id INT UNSIGNED NOT NULL, author_id INT UNSIGNED DEFAULT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_45241D62AAA61A99 (territorial_council_id), INDEX IDX_45241D62F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE political_committee_feed_item (id INT UNSIGNED AUTO_INCREMENT NOT NULL, political_committee_id INT UNSIGNED NOT NULL, author_id INT UNSIGNED DEFAULT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_54369E83C7A72 (political_committee_id), INDEX IDX_54369E83F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE territorial_council_feed_item ADD CONSTRAINT FK_45241D62AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE territorial_council_feed_item ADD CONSTRAINT FK_45241D62F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE political_committee_feed_item ADD CONSTRAINT FK_54369E83C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE political_committee_feed_item ADD CONSTRAINT FK_54369E83F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE user_documents CHANGE type type VARCHAR(25) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE territorial_council_feed_item');
        $this->addSql('DROP TABLE political_committee_feed_item');
        $this->addSql('ALTER TABLE user_documents CHANGE type type VARCHAR(20) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
