<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\UserTimesheetDay;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * Class UserTimesheetDayNormalizer
 */
final class UserTimesheetDayNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'USER_TIMESHEET_DAY_NORMALIZER_ALREADY_CALLED';

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if ($this->isCurrentUserOwner($object)) {
            $context['groups'][] = 'current_user_is_owner';
        }

        $context[self::ALREADY_CALLED] = true;

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

        return $data instanceof UserTimesheetDay;
    }

    /**
     * Check if current user is owner of UserTimesheetDay
     *
     * @param UserTimesheetDay $userTimesheetDay
     *
     * @return bool
     */
    private function isCurrentUserOwner(UserTimesheetDay $userTimesheetDay): bool
    {
        $userTimesheetDayOwner = $userTimesheetDay
            ->getUserTimesheet()
            ->getOwner()
        ;
        $currentUser = $this
            ->tokenStorage
            ->getToken()
            ->getUser()
        ;

        if ($currentUser === $userTimesheetDayOwner) {
            return true;
        }

        return false;
    }
}
