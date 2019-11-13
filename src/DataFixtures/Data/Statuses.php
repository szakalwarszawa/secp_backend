<?php

declare(strict_types=1);

namespace App\DataFixtures\Data;

use ReflectionClass;

/**
 * Common data for UserTimesheetStatusFixtures and UserWorkScheduleStatusFixtures.
 */
class Statuses
{
    /**
     * Get data
     *
     * @param mixed $class
     *
     * @return array
     */
    public static function getAllByClass($class): array
    {
        $reflector = new ReflectionClass($class);
        $classConstants = array_diff(
            $reflector->getConstants(),
            $reflector->getParentClass()->getConstants()
        );

        return [
            $classConstants['REF_STATUS_OWNER_EDIT'] => [
                'title' => 'Edytowana przez pracownika',
                'rules' => [
                    'ROLE_USER' => [
                        $classConstants['REF_STATUS_OWNER_ACCEPT'],
                    ],
                    'ROLE_SECRETARY' => [
                        $classConstants['REF_STATUS_OWNER_ACCEPT'],
                    ],
                    'ROLE_SECTION_MANAGER' => [
                        $classConstants['REF_STATUS_OWNER_ACCEPT'],
                    ],
                    'ROLE_DEPARTMENT_MANAGER' => [
                        $classConstants['REF_STATUS_OWNER_ACCEPT'],
                        $classConstants['REF_STATUS_MANAGER_ACCEPT'],
                    ],
                    'ROLE_HR' => [
                        $classConstants['REF_STATUS_OWNER_ACCEPT'],
                        $classConstants['REF_STATUS_MANAGER_ACCEPT'],
                        $classConstants['REF_STATUS_HR_ACCEPT'],
                    ],
                ]
            ],
            $classConstants['REF_STATUS_OWNER_ACCEPT'] => [
                'title' => 'Zatwierdzona przez pracownika',
                'rules' => [
                    'ROLE_DEPARTMENT_MANAGER' => [
                        $classConstants['REF_STATUS_OWNER_EDIT'],
                        $classConstants['REF_STATUS_MANAGER_ACCEPT'],
                    ],
                    'ROLE_HR' => [
                        $classConstants['REF_STATUS_OWNER_EDIT'],
                        $classConstants['REF_STATUS_MANAGER_ACCEPT'],
                        $classConstants['REF_STATUS_HR_ACCEPT'],
                    ],
                ]
            ],
            $classConstants['REF_STATUS_MANAGER_ACCEPT'] => [
                'title' => 'Zatwierdzona przez przełożonego',
                'rules' => [
                    'ROLE_HR' => [
                        $classConstants['REF_STATUS_OWNER_EDIT'],
                        $classConstants['REF_STATUS_OWNER_ACCEPT'],
                        $classConstants['REF_STATUS_MANAGER_ACCEPT'],
                        $classConstants['REF_STATUS_HR_ACCEPT'],
                    ],
                ],
            ],
            $classConstants['REF_STATUS_HR_ACCEPT'] => [
                'title' => 'Zatwierdzona przez HR',
                'rules' => [
                    'ROLE_HR' => [
                        $classConstants['REF_STATUS_OWNER_EDIT'],
                        $classConstants['REF_STATUS_OWNER_ACCEPT'],
                        $classConstants['REF_STATUS_MANAGER_ACCEPT'],
                    ],
                ],
            ]
        ];
    }
}
