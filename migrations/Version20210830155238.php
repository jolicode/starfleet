<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210830155238 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE notification_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE notification (id INT NOT NULL, target_user_id INT NOT NULL, source_user_id INT NOT NULL, conference_id INT NOT NULL, talk_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, trigger VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF5476CA6C066AFE ON notification (target_user_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAEEB16BFD ON notification (source_user_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA604B8382 ON notification (conference_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA6F0601D5 ON notification (talk_id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA6C066AFE FOREIGN KEY (target_user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAEEB16BFD FOREIGN KEY (source_user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA6F0601D5 FOREIGN KEY (talk_id) REFERENCES talk (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE notification_id_seq CASCADE');
        $this->addSql('DROP TABLE notification');
    }
}
