<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210219145928 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE EXTENSION fuzzystrmatch');
    }

    public function down(Schema $schema): void
    {
    }
}
