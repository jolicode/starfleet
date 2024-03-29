<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201216140149 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_911533c8d1b862b8');
        $this->addSql('ALTER TABLE conference DROP hash');
        $this->addSql('ALTER TABLE users ALTER email TYPE VARCHAR(255)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conference ADD hash VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_911533c8d1b862b8 ON conference (hash)');
        $this->addSql('ALTER TABLE users ALTER email TYPE VARCHAR(180)');
    }
}
