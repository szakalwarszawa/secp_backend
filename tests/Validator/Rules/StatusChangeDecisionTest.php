<?php

declare(strict_types=1);

namespace App\Tests\Validator;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\UserWorkScheduleFixtures;
use App\DataFixtures\UserWorkScheduleStatusFixtures;
use App\Entity\UserWorkSchedule;
use App\Exception\IncorrectStatusChangeException;
use App\Tests\AbstractWebTestCase;
use App\Validator\Rules\StatusChangeDecision;

/**
 * Class StatusChangeDecisionTest
 */
class StatusChangeDecisionTest extends AbstractWebTestCase
{
    /**
     * Test StatusChangeDecision class.
     *
     * @return void
     *
     * @throws IncorrectStatusChangeException
     */
    public function testStatusChangeDecision(): void
    {
        $owner = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        /**
         * Login as ROLE_USER
         */
        $this->loginAsUser($owner, ['ROLE_USER']);
        $authorizationChecker = self::$container->get('security.authorization_checker');
        /**
         * This schedule status is WORK-SCHEDULE-STATUS-OWNER-EDIT, it can be changed by ROLE_USER or ROLE_HR.
         */
        $workSchedule = $this->getEntityFromReference(
            UserWorkScheduleFixtures::REF_FIXED_USER_WORK_SCHEDULE_ADMIN_EDIT
        );
        $this->assertInstanceOf(UserWorkSchedule::class, $workSchedule);

        $statusChangeDecision = new StatusChangeDecision($authorizationChecker);
        $statusChangeDecision->setThrowException(true);

        $workScheduleStatusRef = $this
            ->getEntityFromReference(UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT)
        ;
        /**
         * Check if i can change status directly to 'WORK-SCHEDULE-STATUS-HR-ACCEPT'
         * Current user should not be allowed
         */
        $this->expectException(IncorrectStatusChangeException::class);
        $this->expectExceptionMessage('Brak uprawnień do ustawienia wybranego statusu.');
        $decision = $statusChangeDecision->decide($workSchedule->getStatus(), $workScheduleStatusRef);
        $this->assertFalse($decision);


        $workScheduleStatusRef = $this
            ->getEntityFromReference(UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_ACCEPT)
        ;
        /**
         * But current user should be allowed to change status to WORK-SCHEDULE-STATUS-OWNER-ACCEPT
         */
        $decision = $statusChangeDecision->decide($workSchedule->getStatus(), $workScheduleStatusRef);
        $this->assertTrue($decision);
    }
}
