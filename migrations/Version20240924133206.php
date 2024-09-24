<?php

namespace Migrations;

use App\History\UserActionHistoryTypeEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240924133206 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE user_action_history SET type = :new_type WHERE type = :old_type', [
            'old_type' => 'impersonification_start',
            'new_type' => UserActionHistoryTypeEnum::IMPERSONATION_START->value,
        ]);
        $this->addSql('UPDATE user_action_history SET type = :new_type WHERE type = :old_type', [
            'old_type' => 'impersonification_end',
            'new_type' => UserActionHistoryTypeEnum::IMPERSONATION_END->value,
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE user_action_history SET type = :old_type WHERE type = :new_type', [
            'old_type' => 'impersonification_start',
            'new_type' => UserActionHistoryTypeEnum::IMPERSONATION_START->value,
        ]);
        $this->addSql('UPDATE user_action_history SET type = :old_type WHERE type = :new_type', [
            'old_type' => 'impersonification_end',
            'new_type' => UserActionHistoryTypeEnum::IMPERSONATION_END->value,
        ]);
    }
}
