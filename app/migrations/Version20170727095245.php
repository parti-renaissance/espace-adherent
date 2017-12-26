<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170727095245 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE skills (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX skill_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE summary_skills (summary_id INT NOT NULL, skill_id INT NOT NULL, INDEX IDX_2FD2B63C2AC2D45C (summary_id), INDEX IDX_2FD2B63C5585C142 (skill_id), PRIMARY KEY(summary_id, skill_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE summary_skills ADD CONSTRAINT FK_2FD2B63C2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE summary_skills ADD CONSTRAINT FK_2FD2B63C5585C142 FOREIGN KEY (skill_id) REFERENCES skills (id) ON DELETE CASCADE');

        $sqlTransferSkills = <<<'SQL'
            DROP PROCEDURE IF EXISTS TRANSFER_SKILLS;
            DELIMITER ;;
            CREATE PROCEDURE TRANSFER_SKILLS()
            BEGIN
            
            DECLARE n INT DEFAULT 0;
            DECLARE i INT DEFAULT 0;
            DECLARE transfer_skill_id INT DEFAULT 0;
            DECLARE transfer_summary_id INT DEFAULT 0;
            DECLARE transfer_skill_slug VARCHAR(255);
            DECLARE transfer_skill_name VARCHAR(255);
            SELECT COUNT(*) FROM member_summary_skills INTO n;
            
            SET i=0;
            WHILE i<n DO 
              SET transfer_skill_id = 0;
              SELECT summary_id, name, slug INTO transfer_summary_id, transfer_skill_slug, transfer_skill_name FROM member_summary_skills LIMIT i,1;
              SELECT id INTO transfer_skill_id FROM skills WHERE slug = transfer_skill_slug COLLATE utf8_unicode_ci;
              
              IF transfer_skill_id = 0 THEN
                INSERT INTO skills (`name`, `slug`) VALUES (transfer_skill_name, transfer_skill_slug);
                SET transfer_skill_id = LAST_INSERT_ID();
              END IF;
              
              INSERT INTO summary_skills (summary_id, skill_id) VALUES (transfer_summary_id, transfer_skill_id);
              
              SET i = i + 1;
            END WHILE;
            END;
            ;;

            DELIMITER ;
            CALL TRANSFER_SKILLS();
            DROP PROCEDURE TRANSFER_SKILLS;
SQL;

        $this->addSql($sqlTransferSkills);
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE summary_skills DROP FOREIGN KEY FK_2FD2B63C5585C142');
        $this->addSql('DROP TABLE skills');
        $this->addSql('DROP TABLE summary_skills');
    }
}
