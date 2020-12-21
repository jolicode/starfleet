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

final class Version20201216210507 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conference RENAME COLUMN location TO city');
        $this->addSql('ALTER TABLE conference ALTER city DROP NOT NULL');
        $this->addSql('ALTER TABLE conference ALTER city SET DEFAULT NULL');
        $this->addSql('ALTER TABLE conference ADD country VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conference RENAME COLUMN city TO location');
        $this->addSql('ALTER TABLE conference DROP country');
    }
}
