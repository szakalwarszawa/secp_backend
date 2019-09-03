<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Role;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class UserRoleValidator
 */
class UserRoleValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Validate persisted user roles.
     * Every role added to user must be in roles dictionary (Role::class).
     *
     * @param array $value - persisted new roles
     * @param UserRole $constraint
     *
     * @return void
     */
    public function validate($incomingRoles, Constraint $constraint): void
    {
        if (!is_array($incomingRoles)) {
            return;
        }

        $availableRoles = $this
            ->entityManager
            ->getRepository(Role::class)
            ->findAllAsSimpleArray()
        ;

        $correctRoles = array_intersect($availableRoles, $incomingRoles);
        $incorrectRoles = array_diff($incomingRoles, $correctRoles);

        if ($incorrectRoles) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ value }}', implode(', ', $incorrectRoles))
                ->addViolation()
            ;
        }
    }
}
