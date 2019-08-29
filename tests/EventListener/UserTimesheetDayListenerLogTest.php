<?php

namespace App\Tests\EventSubscriber;

use App\Entity\UserTimesheetDayLog;
use App\Entity\UserTimesheetDay;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;
use Exception;

class UserTimesheetDayListenerLogTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws NotFoundReferencedUserException
     * @throws Exception
     */
    public function apiPutUserTimesheetDay(): void
    {
        $usersDB1 = $this->entityManager->getRepository(UserTimesheetDay::class)->findOneBy(array("id" => 2));
        $t1 = $usersDB1->getWorkingTime();

        $usersDB2 = $this->entityManager->getRepository(UserTimesheetDay::class)->findOneBy(array("id" => 2));
        $usersDB2->setWorkingTime(9.06);
        $this->entityManager->flush();

        $usersDB3 = $this->entityManager->getRepository(UserTimesheetDay::class)->findOneBy(array("id" => 2));
        $t2 = $usersDB3->getWorkingTime();

        $usersDB4 = $this->entityManager->getRepository(UserTimesheetDayLog::class)->findOneBy([], ['id' => 'desc']);
        $notice = $usersDB4->getNotice();
        $this->assertStringContainsString("9.06", $notice);
        $this->assertNotEquals($t1, $t2);
    }
}
