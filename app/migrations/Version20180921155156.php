<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180921155156 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
          CREATE TABLE turnkey_projects_files
          (
            id INT UNSIGNED AUTO_INCREMENT NOT NULL,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            path VARCHAR(255) NOT NULL,
            extension VARCHAR(255) NOT NULL,
            UNIQUE INDEX turnkey_projects_file_slug_extension (slug, extension),
            PRIMARY KEY(id)
          ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
SQL
        );
        $this->addSql(<<<'SQL'
          CREATE TABLE turnkey_project_turnkey_project_file
          (
            turnkey_project_id INT UNSIGNED NOT NULL,
            turnkey_project_file_id INT UNSIGNED NOT NULL,
            INDEX IDX_67BF8377B5315DF4 (turnkey_project_id),
            INDEX IDX_67BF83777D06E1CD (turnkey_project_file_id),
            PRIMARY KEY(turnkey_project_id, turnkey_project_file_id)
          ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
SQL
        );
        $this->addSql('ALTER TABLE turnkey_project_turnkey_project_file ADD CONSTRAINT FK_67BF8377B5315DF4 FOREIGN KEY (turnkey_project_id) REFERENCES turnkey_projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE turnkey_project_turnkey_project_file ADD CONSTRAINT FK_67BF83777D06E1CD FOREIGN KEY (turnkey_project_file_id) REFERENCES turnkey_projects_files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citizen_projects ADD turnkey_project_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE citizen_projects ADD CONSTRAINT FK_6514902B5315DF4 FOREIGN KEY (turnkey_project_id) REFERENCES turnkey_projects (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_6514902B5315DF4 ON citizen_projects (turnkey_project_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE turnkey_project_turnkey_project_file DROP FOREIGN KEY FK_67BF83777D06E1CD');
        $this->addSql('DROP TABLE turnkey_projects_files');
        $this->addSql('DROP TABLE turnkey_project_turnkey_project_file');
        $this->addSql('ALTER TABLE citizen_projects DROP FOREIGN KEY FK_6514902B5315DF4');
        $this->addSql('DROP INDEX IDX_6514902B5315DF4 ON citizen_projects');
        $this->addSql('ALTER TABLE citizen_projects DROP turnkey_project_id');
    }
}
