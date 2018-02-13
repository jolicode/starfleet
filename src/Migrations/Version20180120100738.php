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

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180120100738 extends AbstractMigration implements ContainerAwareInterface
{
    private $updateQueries = [];

    /** @var Container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function preUp(Schema $schema)
    {
        $query = 'SELECT talk_id, conference_id FROM `talks_conferences`';
        $data = $this->connection->prepare($query);
        $data->execute();

        foreach ($data as $row) {
            $talkId = $row['talk_id'];
            $conferenceId = $row['conference_id'];
            $this->updateQueries[] = "UPDATE talk SET conference_id = $conferenceId WHERE id = $talkId";
        }
    }

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE talks_conferences');
        $this->addSql('ALTER TABLE talk ADD conference_id INT DEFAULT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE talk ADD CONSTRAINT FK_9F24D5BB604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id)');
        $this->addSql('CREATE INDEX IDX_9F24D5BB604B8382 ON talk (conference_id)');
        $this->addSql('ALTER TABLE conference CHANGE source source VARCHAR(20) NOT NULL, CHANGE slug slug VARCHAR(255) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE location location VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE tag CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(255) NOT NULL');
    }

    public function postUp(Schema $schema)
    {
        foreach ($this->updateQueries as $query) {
            $this->connection->executeQuery($query);
        }
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE talks_conferences (talk_id INT NOT NULL, conference_id INT NOT NULL, INDEX IDX_2122A8B56F0601D5 (talk_id), INDEX IDX_2122A8B5604B8382 (conference_id), PRIMARY KEY(talk_id, conference_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE talks_conferences ADD CONSTRAINT FK_2122A8B5604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE talks_conferences ADD CONSTRAINT FK_2122A8B56F0601D5 FOREIGN KEY (talk_id) REFERENCES talk (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conference CHANGE source source VARCHAR(20) DEFAULT \'\' NOT NULL COLLATE utf8mb4_general_ci, CHANGE slug slug VARCHAR(255) DEFAULT \'\' NOT NULL COLLATE utf8mb4_general_ci, CHANGE name name VARCHAR(255) DEFAULT \'\' NOT NULL COLLATE utf8mb4_general_ci, CHANGE location location VARCHAR(255) DEFAULT \'\' NOT NULL COLLATE utf8mb4_general_ci');
        $this->addSql('ALTER TABLE tag CHANGE name name VARCHAR(255) DEFAULT \'\' NOT NULL COLLATE utf8mb4_general_ci');
        $this->addSql('ALTER TABLE talk DROP FOREIGN KEY FK_9F24D5BB604B8382');
        $this->addSql('DROP INDEX IDX_9F24D5BB604B8382 ON talk');
        $this->addSql('ALTER TABLE talk DROP conference_id, CHANGE title title VARCHAR(255) DEFAULT \'\' NOT NULL COLLATE utf8mb4_general_ci, CHANGE status status VARCHAR(255) DEFAULT \'\' NOT NULL COLLATE utf8mb4_general_ci');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(255) DEFAULT \'\' NOT NULL COLLATE utf8mb4_general_ci');
    }
}
