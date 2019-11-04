<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\UserTimesheet;
use App\Validator\Rules\StatusChangeDecision;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use App\Exception\IncorrectStatusChangeException;

/**
 * Class UserTimesheetLoggerListener
 */
class UserTimesheetListener
{
    /**
     * @var StatusChangeDecision
     */
    private $statusChangeDecision;

    /**
     * UserTimesheetListener constructor.
     *
     * @param StatusChangeDecision $statusChangeDecision
     */
    public function __construct(StatusChangeDecision $statusChangeDecision)
    {
        $this->statusChangeDecision = $statusChangeDecision;
    }

    /**
     * @param PreUpdateEventArgs $args
     *
     * @throws IncorrectStatusChangeException by StatusChangeDecision::class
     *
     * @todo statusChangeDecision move to validator
     *
     * @return void
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof UserTimesheet) {
            return;
        }

        if (
            $args->hasChangedField('status')
            && $args->getOldValue('status') !== $args->getNewValue('status')
        ) {
            $this
                ->statusChangeDecision
                ->setThrowException(true)
                ->decide(
                    $args->getOldValue('status'),
                    $args->getNewValue('status')
                )
            ;
        }
    }
}
