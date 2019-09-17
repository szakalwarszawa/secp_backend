<?php

declare(strict_types=1);

namespace App\Serializer\GroupsRestrictions\Input;

use App\Serializer\GroupsRestrictions\AbstractIORestriction;
use App\Entity\User;

/**
 * Class UserGroups
 */
final class UserGroups extends AbstractIORestriction implements InputGroupInterface
{
    /**
     * Suffix for group_name.
     *
     * @var string
     */
    protected const SUFFIX = ':input';

    /**
     * {@inheritdoc}
     */
    public static function supports(): string
    {
        return User::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getAll(): array
    {
        return [
            [
                'roles' => ['ROLE_ADMIN', 'ROLE_SUPERVISOR'],
                'group_name' => self::appendSuffix('admin-supervisor'),
            ],
        ];
    }
}
