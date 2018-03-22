<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180322122228 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE conferences_talks');
        $this->addSql('ALTER TABLE conference CHANGE remote_id remote_id VARCHAR(255) DEFAULT NULL, CHANGE cfp_url cfp_url VARCHAR(255) DEFAULT NULL, CHANGE cfp_end_at cfp_end_at DATETIME DEFAULT NULL, CHANGE site_url site_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE submit CHANGE conference_id conference_id INT DEFAULT NULL, CHANGE talk_id talk_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE google_id google_id VARCHAR(255) DEFAULT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE conferences_talks (conference_id INT NOT NULL, talk_id INT NOT NULL, INDEX IDX_ECD4E3F1604B8382 (conference_id), INDEX IDX_ECD4E3F16F0601D5 (talk_id), PRIMARY KEY(conference_id, talk_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conference CHANGE remote_id remote_id VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci, CHANGE cfp_url cfp_url VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci, CHANGE cfp_end_at cfp_end_at DATETIME DEFAULT \'NULL\', CHANGE site_url site_url VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci');
        $this->addSql('ALTER TABLE submit CHANGE conference_id conference_id INT DEFAULT NULL, CHANGE talk_id talk_id INT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE google_id google_id VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci, CHANGE name name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci');
    }
}
