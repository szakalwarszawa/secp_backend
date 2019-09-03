<?php

declare(strict_types=1);

namespace App\Tests\Ldap\Updater;

use App\Tests\AbstractWebTestCase;
use App\Validator\UserRole;
use App\Validator\UserRoleValidator;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

/**
 * Class UserRoleValidatorTest
 */
class UserRoleValidatorTest extends AbstractWebTestCase
{
    /**
     * Test UserRoleValidator class.
     *
     * @return void
     */
    public function testUserRoleValidator(): void
    {
        $userRoleConstraint = new UserRole();
        $context = $this->getMockExecutionContext();
        $context
            ->expects($this->once())
            ->method('buildViolation')
            ->with($userRoleConstraint->message)
            ->willReturn($this->getMockConstraintViolationBuilder())
        ;

        $validator = new UserRoleValidator($this->entityManager);
        $validator->initialize($context);
        $validator->validate(['UNKNOWN_ROLE_NAME_CAUSED_VIOLATION'], $userRoleConstraint);
    }

    /**
     * @return mixed
     */
    private function getMockExecutionContext()
    {
        $context = $this->getMockBuilder(ExecutionContext::class)
            ->disableOriginalConstructor()
            ->setMethods(['buildViolation'])
            ->getMock()
        ;
        return $context;
    }

    /**
     * @return mixed
     */
    private function getMockConstraintViolationBuilder()
    {
        $constraintViolationBuilder = $this->getMockBuilder(ConstraintViolationBuilder::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $constraintViolationBuilder
            ->method('setParameter')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder
            ->method('addViolation');

        return $constraintViolationBuilder;
    }
}
