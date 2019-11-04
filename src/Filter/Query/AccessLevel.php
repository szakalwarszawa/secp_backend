<?php

declare(strict_types=1);

namespace App\Filter\Query;

use Symfony\Component\Security\Core\Security;
use App\Entity\User;
use App\Entity\Utils\UserAware;

/**
 * Class AccessLevel
 */
class AccessLevel
{

    /**
     * @var int
     *
     * User can fetch only his data.
     */
    public const OWN_DATA = 0;

    /**
     * @var int
     *
     * User can fetch all data.
     */
    public const ALL_DATA = 1;

    /**
     * @var int
     *
     * User can fetch entire data of his department.
     */
    public const DEPARTMENT_DATA = 2;

    /**
     * User can fetch entire data of his section.
     */
    public const SECTION_DATA = 3;

    /**
     * @var string
     */
    private const DEFAULT_QUERY = '%s.%s = %s';

    /**
     * @var string
     */
    private const DEFAULT_REFERENCE_QUERY = '%s.%s IN (SELECT %s FROM %s WHERE %s = %s)';

    /**
     * @var User
     */
    private $user;

    /**
     * @var Security
     */
    private $security;


    /**
     * @param User $user
     * @param Security $security
     */
    public function __construct(User $user, Security $security)
    {
        $this->user = $user;
        $this->security = $security;
    }

    /**
     * Returns conditional query based on user role.
     *
     * @param string $targetTableAlias
     * @param UserAware $userAware
     * @param bool $troughReference
     *
     * @return string
     */
    public function getQuery(
        string $targetTableAlias,
        UserAware $userAware,
        bool $troughReference = false
    ): string {
        if ($troughReference) {
            return $this->getReferenceQuery($targetTableAlias, $userAware);
        }

        $user = $this->user;
        $accessLevel = $this->whatCanIFetch();
        switch ($accessLevel) {
            case self::ALL_DATA:
                return '';
            case self::DEPARTMENT_DATA:
                return vsprintf(
                    '%s.%s IN (SELECT id from users where %s=%s)',
                    [
                        $targetTableAlias,
                        $userAware->userFieldName,
                        'department_id',
                        $user->getDepartment()->getId(),
                    ]
                );
            case self::SECTION_DATA:
                return vsprintf(
                    '%s.%s IN (SELECT id from users where %s=%s)',
                    [
                        $targetTableAlias,
                        $userAware->userFieldName,
                        'section_id',
                        $user->getSection()->getId(),
                    ]
                );
            default:
                return vsprintf(self::DEFAULT_QUERY, [
                    $targetTableAlias,
                    $userAware->userFieldName,
                    $this->user->getId(),
                ]);
        }
    }

    /**
     * Returns conditional query based on user role. (trough reference table)
     *
     * @param string $targetTableAlias
     * @param UserAware $userAware
     *
     * @return string
     */
    private function getReferenceQuery(string $targetTableAlias, UserAware $userAware): string
    {
        $accessLevel = $this->whatCanIFetch();
        $user = $this->user;
        $params = [
            $targetTableAlias,
            $userAware->troughForeignKey,
            $userAware->troughReferenceId,
            $userAware->troughReferenceTable,
            $userAware->userFieldName,
        ];
        switch ($accessLevel) {
            case self::ALL_DATA:
                return '';
            case self::DEPARTMENT_DATA:
                $params[] = 'department_id';
                $params[] = $user->getDepartment()->getId();
                return vsprintf(
                    '%s.%s IN (SELECT %s FROM %s WHERE %s IN (SELECT id from users WHERE %s=%s))',
                    $params
                );
            case self::SECTION_DATA:
                $params[] = 'section_id';
                $params[] = $user->getSection()->getId();
                return vsprintf(
                    '%s.%s IN (SELECT %s FROM %s WHERE %s IN (SELECT id from users WHERE %s=%s))',
                    $params
                );
            default:
                $params[] = $user->getId();
                return vsprintf(self::DEFAULT_REFERENCE_QUERY, $params);
        }
    }

    /**
     * Specify access level of user.
     * Based on role hierarchy.
     *
     * @return int
     */
    private function whatCanIFetch(): int
    {
        $security = $this->security;
        if ($security->isGranted('ROLE_HR')) {
            return self::ALL_DATA;
        }
        if ($security->isGranted('ROLE_DEPARTMENT_MANAGER') || $security->isGranted('ROLE_SECRETARY')) {
            return self::DEPARTMENT_DATA;
        }
        if ($security->isGranted('ROLE_SECTION_MANAGER')) {
            return self::SECTION_DATA;
        }

        return self::OWN_DATA;
    }
}
