<?php

declare(strict_types=1);

namespace App\Validator\Rules;

use App\Exception\IncorrectStatusChangeException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Validator\Rules\RuleInterface;

/**
 * Class StatusChangeDecision
 */
class StatusChangeDecision
{
    /**
     * @var string
     */
    private $throwException = false;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function decide(RuleInterface $oldStatus, RuleInterface $newStatus): bool
    {
        $rules = json_decode($oldStatus->getRules());
        foreach ($rules as $key => $rule) {
            if ($this->authorizationChecker->isGranted($key)) {
                if (in_array($newStatus->getId(), $rule, true)) {
                    return true;
                }
            }
        }

        if (!$this->throwException) {
            return false;
        }

        throw new IncorrectStatusChangeException();
    }

    /**
     * Set throwException
     *
     * @param bool $throwException
     *
     * @return void
     */
    public function setThrowException(bool $throwException): void
    {
        $this->throwException = $throwException;
    }
}
