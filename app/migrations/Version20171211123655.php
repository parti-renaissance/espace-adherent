<?php declare(strict_types = 1);

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171211123655 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE reports (
                        id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                        citizen_project_id INT UNSIGNED DEFAULT NULL,
                        reasons JSON NOT NULL,
                        comment LONGTEXT DEFAULT NULL,
                        status VARCHAR(16) NOT NULL,
                        uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
                        type VARCHAR(255) NOT NULL, 
                        INDEX IDX_F11FA745B3584533 (citizen_project_id), 
                        INDEX report_status_idx (status), 
                        UNIQUE INDEX report_uuid_unique (uuid),
                        INDEX report_type_idx (type),
                        PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA745B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE reports');
    }
}
