<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180212214213 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE event_user_documents (event_id INT UNSIGNED NOT NULL, user_document_id INT UNSIGNED NOT NULL, INDEX IDX_7D14491F71F7E88B (event_id), INDEX IDX_7D14491F6A24B1A2 (user_document_id), PRIMARY KEY(event_id, user_document_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE committee_feed_item_user_documents (committee_feed_item_id INT UNSIGNED NOT NULL, user_document_id INT UNSIGNED NOT NULL, INDEX IDX_D269D0AABEF808A3 (committee_feed_item_id), INDEX IDX_D269D0AA6A24B1A2 (user_document_id), PRIMARY KEY(committee_feed_item_id, user_document_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_documents (id INT UNSIGNED AUTO_INCREMENT NOT NULL, original_name VARCHAR(200) NOT NULL, extension VARCHAR(10) NOT NULL, size INT NOT NULL, mime_type VARCHAR(50) NOT NULL, type VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX document_uuid_unique (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_user_documents ADD CONSTRAINT FK_7D14491F71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_user_documents ADD CONSTRAINT FK_7D14491F6A24B1A2 FOREIGN KEY (user_document_id) REFERENCES user_documents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE committee_feed_item_user_documents ADD CONSTRAINT FK_D269D0AABEF808A3 FOREIGN KEY (committee_feed_item_id) REFERENCES committee_feed_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE committee_feed_item_user_documents ADD CONSTRAINT FK_D269D0AA6A24B1A2 FOREIGN KEY (user_document_id) REFERENCES user_documents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE event_user_documents DROP FOREIGN KEY FK_7D14491F6A24B1A2');
        $this->addSql('ALTER TABLE committee_feed_item_user_documents DROP FOREIGN KEY FK_D269D0AA6A24B1A2');
        $this->addSql('DROP TABLE event_user_documents');
        $this->addSql('DROP TABLE committee_feed_item_user_documents');
        $this->addSql('DROP TABLE user_documents');
    }
}
