<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\DataFixtures\UserFixtures;
use App\Entity\DayDefinition;
use App\Entity\User;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use App\Entity\UserWorkScheduleStatus;
use App\Tests\AbstractWebTestCase;
use App\Utils\WorkScheduleCreator;
use DateTimeInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;

/**
 * Class WorkScheduleCreatorTest
 */
class WorkScheduleCreatorTest extends AbstractWebTestCase
{
    /**
     * @var WorkScheduleCreator
     */
    private $workScheduleCreator;

    /**
     * @return void
     * @throws Exception
     */
    public function testCreateWorkSchedule(): void
    {
        $user = $this->getEntityFromReference(UserFixtures::REF_USER_MANAGER);
        $range = $this->workScheduleCreator->specifyRange($user);
        /**
         * @var DateTimeInterface $rangeFrom
         * @var DateTimeInterface $rangeTo
         */
        [$rangeFrom, $rangeTo] = $range;
        $currentScheduleDays = $this
            ->entityManager
            ->getRepository(UserWorkScheduleDay::class)
            ->findWorkDayBetweenDate(
                $user,
                $rangeFrom->format('Y-m-d'),
                $rangeTo->format('Y-m-d')
            );
        $this->assertIsArray($currentScheduleDays);
        $this->assertEmpty($currentScheduleDays);

        $this->workScheduleCreator->createWorkSchedule($user);

        $currentScheduleDays = $this
            ->entityManager
            ->getRepository(UserWorkScheduleDay::class)
            ->findWorkDayBetweenDate(
                $user,
                $rangeFrom->format('Y-m-d'),
                $rangeTo->format('Y-m-d')
            );

        $daysInRange = $rangeFrom->diff($rangeTo)->days;
        $this->assertNotEmpty($currentScheduleDays);
        /**
         * Value $daysInRange is incremented by 1 because ex.
         * there is 2 days difference between 2019-01-01 and 2019-01-03
         * but 3 workScheduleDays has been created (01-01, 01-02, 01-03).
         */
        $this->assertCount($daysInRange + 1, $currentScheduleDays);
    }

    /**
     * specifyRange() test.
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function testSpecifyRange(): void
    {
        /**
         * @var $userEntity User
         */
        $userEntity = $this->getEntityFromReference(UserFixtures::REF_USER_SECRETARY);
        $this->assertInstanceOf(User::class, $userEntity);

        $activeUserSchedule = $this
            ->entityManager
            ->getRepository(UserWorkSchedule::class)
            ->findOneBy([
                'owner' => $userEntity->getId(),
                'status' => UserWorkScheduleStatus::HR_ACCEPTED_STATUS,
            ]);
        $this->assertNotNull($activeUserSchedule);
        $this->assertInstanceOf(UserWorkSchedule::class, $activeUserSchedule);
        $userWorkScheduleDays = $activeUserSchedule->getUserWorkScheduleDays();
        $this->assertNotEmpty($userWorkScheduleDays);

        /**
         * Next reference period range.
         * 4 months range.
         * Should start on the 1st day of month.
         */
        $range = $this->workScheduleCreator->specifyRange($userEntity);
        $this->assertIsArray($range);
        $this->assertNotEmpty($range);
        $this->assertEquals(2, count($range));

        /**
         * @var DateTimeInterface $rangeFrom
         * @var DateTimeInterface $rangeTo
         */
        [$rangeFrom, $rangeTo] = $range;

        $this->assertEquals(1, (int) $rangeFrom->format('d'));

        /**
         * Change last day of his schedule to be after range above.
         * It simulates situation when user has custom (created manually by user) schedule
         * and some day already exists in next period.
         *
         * @var UserWorkScheduleDay $lastScheduleDay
         */
        $lastScheduleDay = $userWorkScheduleDays->last();
        $dayDefinition = $this
            ->entityManager
            ->getRepository(DayDefinition::class)
            ->findOneById(
                $rangeFrom->modify('+3 days')->format('Y-m-d')
            );
        $lastScheduleDay->setDayDefinition($dayDefinition);
        $this->entityManager->flush();

        /**
         * Next try to specify range.
         * Range FROM must be day after date defined in $dayDefinition.
         * Because already created days will be omitted.
         */
        $probablyUpdatedRange = $this->workScheduleCreator->specifyRange($userEntity);
        $this->assertIsArray($probablyUpdatedRange);
        $this->assertNotEmpty($probablyUpdatedRange);
        $this->assertEquals(2, count($probablyUpdatedRange));

        /**
         * @var DateTimeInterface $rangeFrom
         * @var DateTimeInterface $rangeTo
         */
        [$rangeFrom, $rangeTo] = $range;

        $this->assertGreaterThan($rangeFrom, $dayDefinition->getAsDateTime());
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->workScheduleCreator = self::$container->get(WorkScheduleCreator::class);
    }
}
