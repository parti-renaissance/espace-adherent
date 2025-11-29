<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240924123109 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_action_history DROP FOREIGN KEY FK_969D27F32BDDCA0B');
        $this->addSql('DROP INDEX IDX_969D27F32BDDCA0B ON user_action_history');
        $this->addSql('ALTER TABLE user_action_history CHANGE impersonificator_id impersonator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          user_action_history
        ADD
          CONSTRAINT FK_969D27F3D1107CFF FOREIGN KEY (impersonator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_969D27F3D1107CFF ON user_action_history (impersonator_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_action_history DROP FOREIGN KEY FK_969D27F3D1107CFF');
        $this->addSql('DROP INDEX IDX_969D27F3D1107CFF ON user_action_history');
        $this->addSql('ALTER TABLE user_action_history CHANGE impersonator_id impersonificator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          user_action_history
        ADD
          CONSTRAINT FK_969D27F32BDDCA0B FOREIGN KEY (impersonificator_id) REFERENCES administrators (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_969D27F32BDDCA0B ON user_action_history (impersonificator_id)');
    }
}
