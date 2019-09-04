<?php

declare(strict_types=1);

namespace App\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ObjectType;

/**
 * Class ByteObjectType
 */
class ByteObjectType extends ObjectType
{
    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getBlobTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return pg_escape_bytea(serialize($value));
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'byte_object';
    }
}
