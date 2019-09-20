<?php

declare(strict_types=1);

namespace App\Tests\Validator;

use App\Entity\Role;
use App\Tests\AbstractWebTestCase;
use App\Validator\ValueExists;
use App\Validator\ValueExistsValidator;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

/**
 * Class ValueExistsValidatorTest
 */
class ValueExistsValidatorTest extends AbstractWebTestCase
{
    /**
     * Test ValueExistsValidator class.
     * Test should pass when validates wrong value.
     *
     * @return void
     */
    public function testValueExistsValidator(): void
    {
        $valueExistsConstraint = new ValueExists();
        $valueExistsConstraint->entity = Role::class;
        $valueExistsConstraint->searchField = 'name';
        $context = $this->getMockExecutionContext();
        $context
            ->expects($this->once())
            ->method('buildViolation')
            ->with($valueExistsConstraint->message)
            ->willReturn($this->getMockConstraintViolationBuilder())
        ;

        $validator = new ValueExistsValidator($this->entityManager);
        $validator->initialize($context);
        $validator->validate(['UNKNOWN_ROLE_NAME_CAUSED_VIOLATION'], $valueExistsConstraint);
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
