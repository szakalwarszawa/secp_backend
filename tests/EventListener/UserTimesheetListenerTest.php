<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\UserTimesheetStatusFixtures;
use App\Entity\UserTimesheet;
use App\Tests\AbstractWebTestCase;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * Class UserTimesheetListenerTest
 */
class UserTimesheetListenerTest extends AbstractWebTestCase
{
    /**
     * @var int
     */
    private const SAMPLE_ID = 1;

    /**
     * @test
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function firePreUpdateOnUserTimesheetTest(): void
    {
        $user = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        /**
         * Login as ROLE_ADMIN
         */
        $this->loginAsUser($user, ['ROLE_ADMIN']);

        $userTimesheet = $this->entityManager
            ->getRepository(UserTimesheet::class)
            ->findOneBy([
                'id' => self::SAMPLE_ID,
            ]);
        $preUpdateUserTimesheet = clone $userTimesheet;

        $workScheduleStatusRef = $this
            ->getEntityFromReference(UserTimesheetStatusFixtures::REF_STATUS_OWNER_ACCEPT)
        ;
        $userTimesheet->setStatus($workScheduleStatusRef);
        $this->entityManager->flush();

        $refreshedUserTimesheet = $this->entityManager
            ->getRepository(UserTimesheet::class)
            ->findOneBy([
                'id' => self::SAMPLE_ID,
            ]);

        $this->assertNotEquals(
            $preUpdateUserTimesheet->getStatus()->getId(),
            $refreshedUserTimesheet->getStatus()->getId()
        );
    }
}
