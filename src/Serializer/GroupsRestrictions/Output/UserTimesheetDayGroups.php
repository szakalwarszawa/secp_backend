<?php

declare(strict_types=1);

namespace App\Serializer\GroupsRestrictions\Output;

use App\Entity\UserTimesheetDay;
use App\Serializer\GroupsRestrictions\AbstractIORestriction;

/**
 * Class UserTimesheetDayGroups
 */
final class UserTimesheetDayGroups extends AbstractIORestriction implements OutputGroupInterface
{
    /**
     * Suffix for group_name.
     *
     * @var string
     */
    protected const SUFFIX = ':output';

    /**
     * {@inheritdoc}
     */
    public static function supports(): string
    {
        return UserTimesheetDay::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getAll(): array
    {
        return [
            [
                'roles' => ['ROLE_HR'],
                'group_name' => self::appendSuffix('hr'),
            ],
        ];
    }
}
