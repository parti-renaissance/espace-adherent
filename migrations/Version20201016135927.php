<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201016135927 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          user_list_definition_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          CONSTRAINT FK_28CA9F94F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id)');
        $this->addSql('CREATE INDEX IDX_28CA9F94F74563E3 ON adherent_message_filters (user_list_definition_id)');

        $this->addSql('UPDATE adherent_message_filters AS t1
        INNER JOIN user_list_definition AS t2 ON t2.code = t1.user_list_definition
        SET t1.user_list_definition_id = t2.id
        WHERE t1.user_list_definition IS NOT NULL');

        $this->addSql('ALTER TABLE adherent_message_filters DROP user_list_definition');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94F74563E3');
        $this->addSql('DROP INDEX IDX_28CA9F94F74563E3 ON adherent_message_filters');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          user_list_definition VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, 
        DROP 
          user_list_definition_id');
    }
}
