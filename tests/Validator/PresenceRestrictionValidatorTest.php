<?php

declare(strict_types=1);

namespace App\Tests\Validator;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use App\DataFixtures\UserWorkScheduleFixtures;
use App\Entity\DayDefinition;
use App\Entity\PresenceType;
use App\Entity\UserTimesheetDay;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use App\Tests\AbstractWebTestCase;
use Exception;

/**
 * Class ValueExistsValidatorTest
 */
class PresenceRestrictionValidatorTest extends AbstractWebTestCase
{
    /**
     * @var UserWorkSchedule
     */
    private $userWorkSchedule;

    /**
     * @var array
     */
    private $workScheduleWorkingDays = [];

    /**
     * @var array
     */
    private $workScheduleNonWorkingDays = [];

    /**
     * Test case description:
     *      Insert UserTimesheetDay with restricted (today only) presence type.
     *      UserWorkScheduleDay's DayDefinition will be set as today so it must pass validation.
     *
     * @throws Exception
     *
     * @return void
     */
    public function testPresenceRestrictionValidatorCase0(): void
    {
        /** @var UserWorkScheduleDay $userWorkScheduleDay */
        $userWorkScheduleDay = $this->workScheduleWorkingDays[array_rand($this->workScheduleWorkingDays)];
        $this->assertInstanceOf(UserWorkScheduleDay::class, $userWorkScheduleDay);

        /** @var PresenceType $presenceType */
        $presenceType = $this->getEntityFromReference('presence_type_0');

        /**
         * Assume that presence type is available only for today.
         */
        $this->assertEquals('O', $presenceType->getShortName());
        $this->assertEquals(PresenceType::EDIT_RESTRICTION_TODAY, $presenceType->getCreateRestriction());
        $this->assertEquals(PresenceType::EDIT_RESTRICTION_TODAY, $presenceType->getEditRestriction());

        /**
         * Get current date DayDefinition
         */
        $currentDateDayDefinition = $this
            ->entityManager
            ->getRepository(DayDefinition::class)
            ->findTodayDayDefinition()
        ;

        $this->assertInstanceOf(DayDefinition::class, $currentDateDayDefinition);

        /**
         * Change WorkScheduleDay date to be sure it's today.
         */
        $userWorkScheduleDay->setDayDefinition($currentDateDayDefinition);

        $timesheetDay = new UserTimesheetDay();
        $timesheetDay
            ->setUserWorkScheduleDay($userWorkScheduleDay)
            ->setPresenceType($presenceType)
            ->setWorkingTime(8.00)
        ;

        $apiPlatformValidator = self::$container->get('api_platform.validator');
        /**
         * It should not throw exception.
         */
        $apiPlatformValidator->validate($timesheetDay);
    }

    /**
     * Test case description:
     *      Trying to add userTimesheetDay to non-working day but defined presence type
     *      has a restriction that prohibits it from being added to a non-working day.
     *      It should throw an exception.
     *
     * @return void
     */
    public function testPresenceRestrictionValidatorCase1(): void
    {
        $userWorkScheduleDay = $this->workScheduleNonWorkingDays[array_rand($this->workScheduleNonWorkingDays)];
        $this->assertInstanceOf(UserWorkScheduleDay::class, $userWorkScheduleDay);

        /** @var PresenceType $presenceType */
        $presenceType = $this->getEntityFromReference('presence_type_0');

        /**
         * Assume that this is presence type available only for working day.
         */
        $this->assertEquals('O', $presenceType->getShortName());
        $this->assertEquals(PresenceType::RESTRICTION_WORKING_DAY, $presenceType->getEditRestriction());

        $timesheetDay = new UserTimesheetDay();
        $timesheetDay
            ->setUserWorkScheduleDay($userWorkScheduleDay)
            ->setPresenceType($presenceType)
            ->setWorkingTime(8.00)
        ;

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unable to add timesheet day due to edit/add restriction.');

        $apiPlatformValidator = self::$container->get('api_platform.validator');
        $apiPlatformValidator->validate($timesheetDay);
    }

    /**
     *  Test case description:
     *      Trying to add userTimesheetDay to working day but defined presence type
     *      has a restriction that prohibits it from being added before or after today.
     *      It should throw an exception.
     *
     * @return void
     */
    public function testPresenceRestrictionValidatorCase2(): void
    {
        /** @var PresenceType $presenceType */
        $presenceType = $this->getEntityFromReference('presence_type_0');
        $this->assertEquals('O', $presenceType->getShortName());

        $userWorkScheduleDay = $this->workScheduleWorkingDays[array_rand($this->workScheduleWorkingDays)];
        $this->assertInstanceOf(UserWorkScheduleDay::class, $userWorkScheduleDay);

        $timesheetDay = new UserTimesheetDay();
        $timesheetDay
            ->setUserWorkScheduleDay($userWorkScheduleDay)
            ->setPresenceType($presenceType)
            ->setWorkingTime(8.00)
        ;

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unable to add timesheet day due to edit/add restriction.');

        $apiPlatformValidator = self::$container->get('api_platform.validator');
        $apiPlatformValidator->validate($timesheetDay);
    }

    /**
     *  Test case description:
     *      Add userTimesheetDay to working day.
     *      It should not throw exception.
     *
     * @return void
     */
    public function testPresenceRestrictionValidatorCase3(): void
    {
        /**
         * @var PresenceType $presenceType
         *
         * This PresenceType does not have restrictions to it could be possible
         * to add it to any UserSchedule any time.
         */
        $presenceType = $this->getEntityFromReference('presence_type_2');
        $this->assertEquals('S', $presenceType->getShortName());

        $userWorkScheduleDay = $this->workScheduleWorkingDays[array_rand($this->workScheduleWorkingDays)];
        $this->assertInstanceOf(UserWorkScheduleDay::class, $userWorkScheduleDay);

        $timesheetDay = new UserTimesheetDay();
        $timesheetDay
            ->setUserWorkScheduleDay($userWorkScheduleDay)
            ->setPresenceType($presenceType)
            ->setWorkingTime(8.00)
        ;

        $apiPlatformValidator = self::$container->get('api_platform.validator');
        $apiPlatformValidator->validate($timesheetDay);
    }

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        /** @var UserWorkSchedule $userWorkSchedule */
        $userWorkSchedule = $this
            ->getEntityFromReference(UserWorkScheduleFixtures::REF_CURRENT_USER_WORK_SCHEDULE_ADMIN_HR)
        ;
        $this->assertInstanceOf(UserWorkSchedule::class, $userWorkSchedule);
        $this->userWorkSchedule = $userWorkSchedule;

        /** @var UserWorkScheduleDay $userWorkScheduleDay */
        $userWorkScheduleDays = $userWorkSchedule->getUserWorkScheduleDays();
        $this->assertNotEmpty($userWorkScheduleDays);

        /**
         * Loop to find working day.
         */
        foreach ($userWorkScheduleDays as $userWorkScheduleDay) {
            $dayDefinition = $userWorkScheduleDay->getDayDefinition();

            if ($dayDefinition->getWorkingDay()) {
                $this->workScheduleWorkingDays[] = $userWorkScheduleDay;

                continue;
            }

            $this->workScheduleNonWorkingDays[] = $userWorkScheduleDay;
        }
    }
}
