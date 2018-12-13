<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181213131031 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conference CHANGE remote_id remote_id VARCHAR(255) DEFAULT NULL, CHANGE end_at end_at DATETIME DEFAULT NULL, CHANGE cfp_url cfp_url VARCHAR(255) DEFAULT NULL, CHANGE cfp_end_at cfp_end_at DATETIME DEFAULT NULL, CHANGE site_url site_url VARCHAR(255) DEFAULT NULL, CHANGE hash hash VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE submit CHANGE conference_id conference_id INT DEFAULT NULL, CHANGE talk_id talk_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tag ADD selected TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE google_id google_id VARCHAR(255) DEFAULT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conference CHANGE remote_id remote_id VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci, CHANGE hash hash VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci, CHANGE end_at end_at DATETIME DEFAULT \'NULL\', CHANGE cfp_url cfp_url VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci, CHANGE cfp_end_at cfp_end_at DATETIME DEFAULT \'NULL\', CHANGE site_url site_url VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci');
        $this->addSql('ALTER TABLE submit CHANGE conference_id conference_id INT DEFAULT NULL, CHANGE talk_id talk_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tag DROP selected');
        $this->addSql('ALTER TABLE user CHANGE google_id google_id VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci, CHANGE name name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci');
    }
}
