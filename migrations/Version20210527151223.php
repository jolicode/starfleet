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
final class Version20210527151223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE submit DROP CONSTRAINT FK_3F31B343604B8382');
        $this->addSql('ALTER TABLE submit DROP CONSTRAINT FK_3F31B3436F0601D5');
        $this->addSql('ALTER TABLE submit ALTER conference_id SET NOT NULL');
        $this->addSql('ALTER TABLE submit ALTER talk_id SET NOT NULL');
        $this->addSql('ALTER TABLE submit ADD CONSTRAINT FK_3F31B343604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submit ADD CONSTRAINT FK_3F31B3436F0601D5 FOREIGN KEY (talk_id) REFERENCES talk (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE submit DROP CONSTRAINT fk_3f31b343604b8382');
        $this->addSql('ALTER TABLE submit DROP CONSTRAINT fk_3f31b3436f0601d5');
        $this->addSql('ALTER TABLE submit ALTER conference_id DROP NOT NULL');
        $this->addSql('ALTER TABLE submit ALTER talk_id DROP NOT NULL');
        $this->addSql('ALTER TABLE submit ADD CONSTRAINT fk_3f31b343604b8382 FOREIGN KEY (conference_id) REFERENCES conference (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submit ADD CONSTRAINT fk_3f31b3436f0601d5 FOREIGN KEY (talk_id) REFERENCES talk (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
