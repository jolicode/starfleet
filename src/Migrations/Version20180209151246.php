<?php

declare(strict_types=1);

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180209151246 extends AbstractMigration
{
    private $insertTalkQueries = [];

    public function preUp(Schema $schema): void
    {
        $query = 'SELECT * FROM `talk`';
        $data = $this->connection->prepare($query);
        $data->execute();

        foreach ($data as $row) {
            $this->insertTalkQueries[] = sprintf(
                'INSERT INTO talk (id, title, intro, created_at, updated_at) VALUES (%s, %s, %s, "%s", "%s")',
                $row['id'],
                $this->connection->quote($row['title']),
                $this->connection->quote($row['intro']),
                $row['created_at'],
                $row['updated_at']
            );
        }
    }

    public function postUp(Schema $schema): void
    {
        foreach ($this->insertTalkQueries as $query) {
            $this->connection->executeQuery($query);
        }

        $this->connection->executeQuery('ALTER TABLE submit ADD CONSTRAINT FK_3F31B3436F0601D5 FOREIGN KEY (talk_id) REFERENCES talk (id)');
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE talk RENAME submit');
        $this->addSql('ALTER TABLE submit ADD talk_id INT DEFAULT NULL,  DROP title, DROP intro');
        $this->addSql('UPDATE submit set talk_id = id');
        $this->addSql('ALTER TABLE submit CHANGE talk_id talk_id INT NOT NULL');
        $this->addSql('ALTER TABLE submit ADD INDEX IDX_3F31B343604B8382 (conference_id)');
        $this->addSql('ALTER TABLE submit ADD INDEX IDX_3F31B3436F0601D5 (talk_id)');
        $this->addSql('ALTER TABLE submit ADD CONSTRAINT FK_3F31B343604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id)');
        $this->addSql('ALTER TABLE submit DROP FOREIGN KEY FK_9F24D5BB604B8382');
        $this->addSql('ALTER TABLE submit DROP INDEX IDX_9F24D5BB604B8382');

        $this->addSql('ALTER TABLE talks_users RENAME submits_users');
        $this->addSql('ALTER TABLE submits_users CHANGE talk_id submit_id INT NOT NULL');
        $this->addSql('ALTER TABLE submits_users ADD INDEX IDX_B2218AE08AAB0BD7 (submit_id)');
        $this->addSql('ALTER TABLE submits_users ADD INDEX IDX_B2218AE0A76ED395 (user_id)');
        $this->addSql('ALTER TABLE submits_users ADD CONSTRAINT FK_B2218AE08AAB0BD7 FOREIGN KEY (submit_id) REFERENCES submit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE submits_users ADD CONSTRAINT FK_B2218AE0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE submits_users DROP FOREIGN KEY FK_DE7613EE6F0601D5');
        $this->addSql('ALTER TABLE submits_users DROP FOREIGN KEY FK_DE7613EEA76ED395');
        $this->addSql('ALTER TABLE submits_users DROP INDEX IDX_DE7613EE6F0601D5');
        $this->addSql('ALTER TABLE submits_users DROP INDEX IDX_DE7613EEA76ED395');

        $this->addSql('CREATE TABLE talk (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, intro LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // We do not keep data for the down migration.
        // Use a backup to restore data instead.

        $this->addSql('ALTER TABLE submits_users DROP FOREIGN KEY FK_B2218AE08AAB0BD7');
        $this->addSql('CREATE TABLE talks_users (talk_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_DE7613EE6F0601D5 (talk_id), INDEX IDX_DE7613EEA76ED395 (user_id), PRIMARY KEY(talk_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('ALTER TABLE talks_users ADD CONSTRAINT FK_DE7613EE6F0601D5 FOREIGN KEY (talk_id) REFERENCES talk (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE talks_users ADD CONSTRAINT FK_DE7613EEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE submit');
        $this->addSql('DROP TABLE submits_users');
        $this->addSql('ALTER TABLE talk ADD conference_id INT DEFAULT NULL, ADD submitted_at DATETIME NOT NULL, ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE talk ADD CONSTRAINT FK_9F24D5BB604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id)');
        $this->addSql('CREATE INDEX IDX_9F24D5BB604B8382 ON talk (conference_id)');
    }
}
