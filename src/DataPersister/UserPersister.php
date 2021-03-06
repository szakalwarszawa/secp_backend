<?php

declare(strict_types=1);

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class UserPersister
 */
final class UserPersister implements ContextAwareDataPersisterInterface
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
     * {@inheritDoc}
     */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof User;
    }

    /**
     * {@inheritDoc}
     */
    public function persist($data, array $context = [])
    {
        $this->manageUserProfileSettings($data);

        $this->entityManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function remove($data, array $context = [])
    {
    }

    /**
     * Set correctly user's working time attributes
     * based on user's profile. It prevents to change that attributes when user
     * has ex. default profile.
     *
     * @param User $user
     *
     * @return void
     */
    private function manageUserProfileSettings(User $user): void
    {
        $workScheduleProfile = $user->getDefaultWorkScheduleProfile();
        $profileProperties = $workScheduleProfile->getProperties();
        /**
         * Individual profile
         */
        if ($profileProperties['dayStartTimeFrom'] !== $profileProperties['dayStartTimeTo']) {
            $user
                ->setDayStartTimeFrom($user->getDayStartTimeTo())
                ->setDayEndTimeFrom($user->getDayEndTimeTo())
            ;
        }
        /**
         * Default profile
         */
        if (!$profileProperties['dayStartTimeFrom'] && !$profileProperties['dayStartTimeTo']) {
            $user
                ->setDayStartTimeFrom($workScheduleProfile->getDayStartTimeFrom())
                ->setDayStartTimeTo($workScheduleProfile->getDayStartTimeTo())
                ->setDayEndTimeFrom($workScheduleProfile->getDayEndTimeFrom())
                ->setDayEndTimeTo($workScheduleProfile->getDayEndTimeTo())
            ;
        }

        $this->entityManager->persist($user);
    }
}
