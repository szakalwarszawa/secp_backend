<?php

declare(strict_types=1);

namespace App\Serializer\GroupsRestrictions;

use App\Serializer\GroupsRestrictions\Input\InputGroupInterface;
use App\Serializer\GroupsRestrictions\Input\UserGroups;
use App\Serializer\GroupsRestrictions\Output\OutputGroupInterface;
use App\Serializer\GroupsRestrictions\Output\UserTimesheetDayGroups;
use stdClass;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class GroupRestriction
 */
final class GroupRestriction
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var string[]
     */
    private $registeredRestrictions;

    /**
     * @var string[]
     */
    public $initializedRestrictions;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->registeredRestrictions = [
            UserTimesheetDayGroups::class,
            UserGroups::class,
        ];
    }

    /**
     * Adds IO restrictions based on context.
     * Available restrictions are defined in `registeredRestrictions`.
     *
     * @param stdClass|array $context
     * @param bool $normalization
     *
     * @throws InvalidArgumentException when $context is not an array or stdClass instance
     *
     * @return array
     */
    public function addIOGroupRestrictions($context, bool $normalization): array
    {
        if (!$context instanceof stdClass && !is_array($context)) {
            throw new InvalidArgumentException('Context MUST be array or stdClass object.');
        }

        if (!$context instanceof stdClass) {
            $context = (object) $context;
        }

        foreach ($this->registeredRestrictions as $restriction) {
            if ($this->isContextSupported($context, $restriction, $normalization)) {
                foreach ($restriction::getAll() as $condition) {
                    if ($this->authorizationChecker->isGranted($condition['roles'])
                        && !in_array($condition['group_name'], $context->groups)
                    ) {
                        $context->groups[] = $condition['group_name'];
                        $this->initializedRestrictions[] = $restriction;
                    }
                }
            }
        }

        return (array) $context;
    }

    /**
     * @param stdClass $context
     * @param string $restrictionClass
     * @param bool $normalization
     *
     * @return bool
     */
    private function isContextSupported(stdClass $context, string $restrictionClass, bool $normalization): bool
    {
        if ($restrictionClass::supports() === $context->resource_class) {
            if (new $restrictionClass() instanceof OutputGroupInterface && $normalization) {
                return true;
            }

            if (new $restrictionClass() instanceof InputGroupInterface && !$normalization) {
                return true;
            }

            return false;
        }

        return false;
    }
}
