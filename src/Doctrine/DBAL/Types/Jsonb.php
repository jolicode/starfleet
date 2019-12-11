<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * Implementation of PostgreSql JSONB data type.
 *
 * @see https://www.postgresql.org/docs/9.4/static/datatype-json.html
 * @since 0.1
 *
 * @author Martin Georgiev <martin.georgiev@gmail.com>
 * Copied from martin-georgiev/postgresql-for-doctrine package
 */
class Jsonb extends Type
{
    /**
     * @var string
     */
    protected const TYPE_NAME = 'jsonb';

    /**
     * Converts a value from its PHP representation to its database representation of the type.
     *
     * @param array|object|null $value the value to convert
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        return $this->transformToPostgresJson($value);
    }

    /**
     * Converts a value from its database representation to its PHP representation of this type.
     *
     * @param string|null $value the value to convert
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if (null === $value) {
            return null;
        }

        return $this->transformFromPostgresJson($value);
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        self::throwExceptionIfTypeNameNotConfigured();

        return $platform->getDoctrineTypeMapping(static::TYPE_NAME);
    }

    public function getName(): string
    {
        self::throwExceptionIfTypeNameNotConfigured();

        return static::TYPE_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return false;
    }

    /**
     * @param mixed $phpValue Value bus be suitable for JSON encoding
     *
     * @throws ConversionException When given value cannot be encoded
     */
    protected function transformToPostgresJson($phpValue): string
    {
        $postgresValue = json_encode($phpValue);

        if (false === $postgresValue) {
            throw new ConversionException(sprintf('Value %s can\'t be resolved to valid JSON', var_export($phpValue, true)));
        }

        return $postgresValue;
    }

    protected function transformFromPostgresJson(string $postgresValue): array
    {
        return json_decode($postgresValue, true);
    }

    private static function throwExceptionIfTypeNameNotConfigured(): void
    {
        if (null === static::TYPE_NAME) {
            throw new \LogicException(sprintf('Doctrine type defined in class %s has no meaningful value for TYPE_NAME constant', self::class));
        }
    }
}
