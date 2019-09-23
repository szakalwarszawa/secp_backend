<?php

declare(strict_types=1);

namespace App\Tests\Filter\Query;

use App\DataFixtures\UserFixtures;
use App\Entity\Utils\UserAware;
use App\Filter\Query\AccessLevel;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

/**
 * Class AccessLevelTest
 */
class AccessLevelTest extends AbstractWebTestCase
{
    /**
     * test AccessLevel
     */
    public function testAccessLevel()
    {
        $userAdmin = $this->fixtures->getReference(UserFixtures::REF_USER_ADMIN);
        $this->loginAsUser($userAdmin, ['ROLE_DEPARTMENT_MANAGER']);


        $this->assertFalse($this->security->isGranted('ROLE_ADMIN'));
        $this->assertFalse($this->security->isGranted('ROLE_SUPERVISOR'));
        $this->assertFalse($this->security->isGranted('ROLE_HR'));
        $this->assertTrue($this->security->isGranted('ROLE_USER'));
        $this->assertTrue($this->security->isGranted('ROLE_DEPARTMENT_MANAGER'));
        $this->assertFalse($this->security->isGranted('ROLE_NOT_EXISTS'));

        $accessLevel = new AccessLevel($userAdmin, $this->security);
        $userAware = new UserAware();
        $userAware->userFieldName ='user_id';
        $conditionQuery = $accessLevel->getQuery('s', $userAware);

        $expectedQuery = sprintf(
            's.user_id IN (SELECT id from users where department_id=%s)',
            $userAdmin->getDepartment()->getId()
        );
        $this->assertEquals($expectedQuery, $conditionQuery);
    }
}
