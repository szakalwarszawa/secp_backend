<?php

namespace App\Tests\EventSubscriber;

use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetLog;
use App\Tests\AbstractWebTestCase;

class UserTimesheetListenerTest extends AbstractWebTestCase
{
    private const SAMPLE_STATUS = 3;
    private const SAMPLE_ID = 1;

    /**
     * @test
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function firePreUpdateOnUserTimesheetTest(): void
    {
        $UserTimesheet = $this->entityManager->getRepository(UserTimesheet::class)->findOneBy(
            array("id" => self::SAMPLE_ID)
        );

        $status = $UserTimesheet->getStatus();
        $UserTimesheet->setStatus(self::SAMPLE_STATUS);
        $this->entityManager->flush();

        $statusChanged = $UserTimesheet->getStatus();
        $UserTimesheetLog = $this->entityManager->getRepository(UserTimesheetLog::class)->findOneBy(
            [], ['id' => 'desc']);
        $notice = $UserTimesheetLog->getNotice();

        $this->assertStringContainsString(self::SAMPLE_STATUS, $notice);
        $this->assertNotEquals($status, $statusChanged);
    }
}
