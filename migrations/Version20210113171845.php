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

final class Version20210113171845 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conference ADD tags JSONB');

        $queryTags = 'SELECT id, name FROM tag';
        $stmt = $this->connection->prepare($queryTags);
        $allTags = $stmt->executeQuery()->fetchAllKeyValue();

        $queryConferenceIds = 'SELECT id FROM conference';
        $stmt = $this->connection->prepare($queryConferenceIds);
        $allConferenceIds = $stmt->executeQuery()->fetchFirstColumn();

        $queryConferenceTagIds = 'SELECT tag_id FROM conferences_tags WHERE conference_id = :id';
        $tagIdsStatement = $this->connection->prepare($queryConferenceTagIds);

        foreach ($allConferenceIds as $conferenceId) {
            $tagIdsStatement->bindValue('id', $conferenceId);
            $tagIds = array_flip($tagIdsStatement->executeQuery()->fetchFirstColumn());
            $tagsArray = array_values(array_intersect_key($allTags, $tagIds));

            $this->addSql('UPDATE conference SET tags = :tags WHERE id = :id', ['tags' => json_encode($tagsArray), 'id' => $conferenceId]);
        }

        $this->addSql('ALTER TABLE conferences_tags DROP CONSTRAINT fk_48e3e609bad26311');
        $this->addSql('DROP SEQUENCE tag_id_seq CASCADE');
        $this->addSql('DROP TABLE conferences_tags');
        $this->addSql('DROP TABLE tag');
        $this->addSql('ALTER TABLE conference ALTER tags SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
