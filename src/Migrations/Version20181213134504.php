<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181213134504 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE conference (id INT AUTO_INCREMENT NOT NULL, remote_id VARCHAR(255) DEFAULT NULL, hash VARCHAR(255) DEFAULT NULL, source VARCHAR(20) NOT NULL, slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, location VARCHAR(255) NOT NULL, start_at DATETIME NOT NULL, end_at DATETIME DEFAULT NULL, cfp_url VARCHAR(255) DEFAULT NULL, cfp_end_at DATETIME DEFAULT NULL, site_url VARCHAR(255) DEFAULT NULL, article_url LONGTEXT DEFAULT NULL, attended TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_911533C8989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE conferences_tags (conference_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_48E3E609604B8382 (conference_id), INDEX IDX_48E3E609BAD26311 (tag_id), PRIMARY KEY(conference_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE submit (id INT AUTO_INCREMENT NOT NULL, conference_id INT DEFAULT NULL, talk_id INT DEFAULT NULL, submitted_at DATE NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_3F31B343604B8382 (conference_id), INDEX IDX_3F31B3436F0601D5 (talk_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE submits_users (submit_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B2218AE08AAB0BD7 (submit_id), INDEX IDX_B2218AE0A76ED395 (user_id), PRIMARY KEY(submit_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, selected TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE talk (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, intro LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, google_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, bio LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('ALTER TABLE conferences_tags ADD CONSTRAINT FK_48E3E609604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conferences_tags ADD CONSTRAINT FK_48E3E609BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE submit ADD CONSTRAINT FK_3F31B343604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id)');
        $this->addSql('ALTER TABLE submit ADD CONSTRAINT FK_3F31B3436F0601D5 FOREIGN KEY (talk_id) REFERENCES talk (id)');
        $this->addSql('ALTER TABLE submits_users ADD CONSTRAINT FK_B2218AE08AAB0BD7 FOREIGN KEY (submit_id) REFERENCES submit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE submits_users ADD CONSTRAINT FK_B2218AE0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conferences_tags DROP FOREIGN KEY FK_48E3E609604B8382');
        $this->addSql('ALTER TABLE submit DROP FOREIGN KEY FK_3F31B343604B8382');
        $this->addSql('ALTER TABLE submits_users DROP FOREIGN KEY FK_B2218AE08AAB0BD7');
        $this->addSql('ALTER TABLE conferences_tags DROP FOREIGN KEY FK_48E3E609BAD26311');
        $this->addSql('ALTER TABLE submit DROP FOREIGN KEY FK_3F31B3436F0601D5');
        $this->addSql('ALTER TABLE submits_users DROP FOREIGN KEY FK_B2218AE0A76ED395');
        $this->addSql('DROP TABLE conference');
        $this->addSql('DROP TABLE conferences_tags');
        $this->addSql('DROP TABLE submit');
        $this->addSql('DROP TABLE submits_users');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE talk');
        $this->addSql('DROP TABLE user');
    }
}
