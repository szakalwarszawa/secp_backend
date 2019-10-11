<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\UserTimesheetDayLog;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * Class UserTimesheetDayLogNormalizer
 * Short manual
 *  Each UserTimesheetDayLog object has `trigger` property which identifies
 *  element that has been changed in UserTimesheetDay. This class manipulates
 *  returned object (overrides any property) if ERASE_TRIGGERS_LIST contains that trigger.
 */
final class UserTimesheetDayLogNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'USER_TIMESHEET_DAY_LOG_NORMALIZER_ALREADY_CALLED';

    /**
     * Triggers and replacements.
     *
     * ex. UserTimesheetDayLog trigger is `absenceType` then in normalize()
     * this object is manipulated.
     *
     * @var array
     */
    private const ERASE_TRIGGERS_LIST = [
        'absenceType' => 'Zmieniono typ nieobecności',
    ];

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        if (!($this->isCurrentUserOwner($object) || $this->authorizationChecker->isGranted('ROLE_HR'))
            && array_key_exists($object->getTrigger(), self::ERASE_TRIGGERS_LIST)
        ) {
            $object->setNotice(self::ERASE_TRIGGERS_LIST[$object->getTrigger()]);
        }

        return $this
            ->normalizer
            ->normalize($object, $format, $context)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = [])
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof UserTimesheetDayLog;
    }

    /**
     * Check if current user is owner of UserTimesheetDayLog
     *
     * @param UserTimesheetDayLog $userTimesheetDayLog
     *
     * @return bool
     */
    private function isCurrentUserOwner(UserTimesheetDayLog $userTimesheetDayLog): bool
    {
        $userTimesheetDayOwner = $userTimesheetDayLog
            ->getUserTimesheetDay()
            ->getUserTimesheet()
            ->getOwner()
            ->getUsername()
        ;

        $currentUser = $this
            ->tokenStorage
            ->getToken()
            ->getUser()
            ->getUsername()
        ;

        return $currentUser === $userTimesheetDayOwner;
    }
}
