<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Tests\AbstractWebTestCase;
use App\Utils\ReferencePeriod;
use DateTime;
use DateTimeInterface;
use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Class ReferencePeriodTest
 */
class ReferencePeriodTest extends AbstractWebTestCase
{
    /**
     * @var ReferencePeriod
     */
    private $referencePeriod;

    /**
     * getCurrentPeriod() test
     *
     * @return void
     * @throws Exception
     */
    public function testGetCurrentPeriod(): void
    {
        $currentPeriod = $this->referencePeriod->getCurrentPeriod();
        /**
         * Period is an date range (FROM - TO)
         */
        $this->assertCount(2, $currentPeriod);

        [$rangeFrom, $rangeTo] = $currentPeriod;
        $this->assertInstanceOf(DateTimeInterface::class, $rangeFrom);
        $this->assertInstanceOf(DateTimeInterface::class, $rangeTo);

        $currentDate = new DateTime();

        /**
         * Current date must be between period ranges.
         */
        $this->assertGreaterThanOrEqual($rangeFrom, $currentDate);
        $this->assertLessThanOrEqual($rangeTo, $currentDate);
    }

    /**
     * getPeriods() test
     *
     * @return void
     * @throws ReflectionException
     */
    public function testGetPeriods(): void
    {
        $allPeriods = $this->referencePeriod->getPeriods();
        $this->assertIsArray($allPeriods);

        $reflection = new ReflectionClass(ReferencePeriod::class);
        /**
         * Constant is private.
         */
        $periodsCount = $reflection->getConstant('PERIODS_COUNT');
        $this->assertGreaterThan(1, count($allPeriods));
        $this->assertEquals($periodsCount, count($allPeriods));
    }

    /**
     * getNextPeriod() test
     *
     * @return void
     * @throws Exception
     */
    public function testGetNextPeriod(): void
    {
        $currentPeriod = $this->referencePeriod->getCurrentPeriod();
        $nextPeriod = $this->referencePeriod->getNextPeriod();
        $this->assertCount(2, $nextPeriod);

        /**
         * @var $currentPeriodFrom DateTimeInterface
         * @var $currentPeriodTo DateTimeInterface
         * @var $nextPeriodFrom DateTimeInterface
         * @var $nextPeriodTo DateTimeInterface
         */
        [$currentPeriodFrom, $currentPeriodTo] = $currentPeriod;
        [$nextPeriodFrom, $nextPeriodTo] = $nextPeriod;

        $this->assertGreaterThan($currentPeriodTo, $nextPeriodFrom);
        $this->assertLessThan($nextPeriodTo, $currentPeriodFrom);
        $daysDiff = $currentPeriodTo->diff($nextPeriodFrom)->days;
        /**
         * Next period should start day after current.
         */
        $this->assertEquals(1, $daysDiff);
        $this->assertEquals($currentPeriodTo, $nextPeriodFrom->modify('-1 day'));
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->referencePeriod = self::$container->get(ReferencePeriod::class);
    }
}
