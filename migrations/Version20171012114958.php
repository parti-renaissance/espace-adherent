<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171012114958 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE saved_board_members (board_member_owner_id INT NOT NULL, board_member_saved_id INT NOT NULL, INDEX IDX_32865A32FDCCD727 (board_member_owner_id), INDEX IDX_32865A324821D202 (board_member_saved_id), PRIMARY KEY(board_member_owner_id, board_member_saved_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE saved_board_members ADD CONSTRAINT FK_32865A32FDCCD727 FOREIGN KEY (board_member_owner_id) REFERENCES board_member (id)');
        $this->addSql('ALTER TABLE saved_board_members ADD CONSTRAINT FK_32865A324821D202 FOREIGN KEY (board_member_saved_id) REFERENCES board_member (id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE saved_board_members');
    }
}
