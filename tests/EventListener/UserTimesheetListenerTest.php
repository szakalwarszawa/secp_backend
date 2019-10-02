<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\DataFixtures\UserTimesheetStatusFixtures;
use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetLog;
use App\Tests\AbstractWebTestCase;
use App\DataFixtures\UserFixtures;

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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
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

        $status = $userTimesheet->getStatus();
        $workScheduleStatusRef = $this
            ->getEntityFromReference(UserTimesheetStatusFixtures::REF_STATUS_OWNER_ACCEPT)
        ;
        $userTimesheet->setStatus($workScheduleStatusRef);
        $this->entityManager->flush();

        $statusChanged = $userTimesheet->getStatus();
        $userTimesheetLog = $this->entityManager
            ->getRepository(UserTimesheetLog::class)
            ->findOneBy(
                [],
                ['id' => 'desc']
            );

        $this->assertNotNull($userTimesheetLog);
        $notice = $userTimesheetLog->getNotice();
        $this->assertStringContainsString('Zmieniono status z: ' . $status->getId() .' na: ' .
            $statusChanged->getId(), $notice);
        $this->assertNotEquals($status, $statusChanged);
    }
}
