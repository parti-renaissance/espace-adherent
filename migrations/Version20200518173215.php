<?php

namespace Migrations;

use App\Entity\UserListDefinitionEnum;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200518173215 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE elected_representative SET has_followed_training = 0 WHERE has_followed_training IS NULL');
        $this->addSql('CREATE TABLE elected_representative_user_list_definition (
          elected_representative_id INT NOT NULL,
          user_list_definition_id INT UNSIGNED NOT NULL,
          INDEX IDX_A9C53A24D38DA5D3 (elected_representative_id),
          INDEX IDX_A9C53A24F74563E3 (user_list_definition_id),
          PRIMARY KEY(
            elected_representative_id, user_list_definition_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_list_definition (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          type VARCHAR(50) NOT NULL,
          label VARCHAR(100) NOT NULL,
          UNIQUE INDEX user_list_definition_type_label_unique (type, label),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition
        ADD
          CONSTRAINT FK_A9C53A24D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition
        ADD
          CONSTRAINT FK_A9C53A24F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative
        CHANGE
          has_followed_training has_followed_training TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function postUp(Schema $schema)
    {
        foreach (UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE_LABELS as $label) {
            $this->connection->insert('user_list_definition', [
                'type' => UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE,
                'label' => $label,
            ]);
        }

        $this->connection->executeQuery(
            'INSERT INTO elected_representative_user_list_definition (elected_representative_id, user_list_definition_id)
            SELECT er.id, uld.id
            FROM elected_representative er, user_list_definition uld
            WHERE er.is_supporting_la_rem = 1 AND uld.label = ?',
            [UserListDefinitionEnum::LABEL_ELECTED_REPRESENTATIVE_SUPPORTING_LA_REM]
        );
        $this->connection->executeQuery('ALTER TABLE elected_representative DROP is_supporting_la_rem');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_user_list_definition DROP FOREIGN KEY FK_A9C53A24F74563E3');
        $this->addSql('DROP TABLE elected_representative_user_list_definition');
        $this->addSql('DROP TABLE user_list_definition');
        $this->addSql('ALTER TABLE
          elected_representative
        CHANGE
          has_followed_training has_followed_training TINYINT(1) DEFAULT NULL');
    }
}
