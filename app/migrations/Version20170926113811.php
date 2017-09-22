<?php

namespace Migrations;

use AppBundle\DataFixtures\ORM\LoadMailjetTemplatesData;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Ramsey\Uuid\Uuid;

class Version20170926113811 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE mailjet_templates (id INT UNSIGNED AUTO_INCREMENT NOT NULL, message_class VARCHAR(55) NOT NULL, sender_email VARCHAR(255) NOT NULL, sender_name VARCHAR(100) NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_A66167CB12EDE674 (message_class), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function postUp(Schema $schema)
    {
        foreach (LoadMailjetTemplatesData::MAILJET_TEMPLATES as $messageClass => $data) {
            $this->connection->insert('mailjet_templates', [
                'uuid' => Uuid::uuid4(),
                'message_class' => $messageClass,
                'sender_email' => $data['senderEmail'],
                'sender_name' => $data['senderName'],
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE mailjet_templates');
    }
}
