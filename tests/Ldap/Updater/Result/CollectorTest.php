<?php

namespace App\Tests\Ldap\Updater;

use App\Entity\Department;
use App\Ldap\Import\Updater\Result\Result;
use App\Ldap\Import\Updater\Result\Types;
use App\Ldap\Import\Updater\Result\Actions;
use App\Ldap\Import\Updater\Result\Collector;
use App\Tests\AbstractWebTestCase;
use LdapTools\Object\LdapObject;

/**
 * Class CollectorTest
 */
class CollectorTest extends AbstractWebTestCase
{
    /**
     * Test Collector class.
     */
    public function testResultCollector(): void
    {
        $departmentExists = true;
        $message1 = sprintf(
            'Department %s has been %s.',
            'Nazwa departamentu 1',
            $departmentExists ? 'updated' : 'created'
        );
        $departamentCreatedCorrectly = new Result(
            Department::class,
            Types::SUCCESS,
            '',
            $message1,
            $departmentExists ? Actions::UPDATE : Actions::CREATE
        );

        $userNotCreatedCorrectly = new Result(
            LdapObject::class,
            Types::FAIL,
            '',
            sprintf('User`s %s has no first name', 'janusz_tracz'),
            Actions::IGNORE
        );

        $collector = new Collector();
        $collector->add($departamentCreatedCorrectly);
        $collector->add($userNotCreatedCorrectly);

        /**
         * There must be only one failed type object.
         */
        $this->assertCount(1, $collector->getFailed());

        /**
         * Result's className of first failed type must be same as defined in $userNotCreatedCorrectly.
         */
        $firstFailedElement = current($collector->getFailed());
        $this->assertEquals(LdapObject::class, $firstFailedElement->getClassName());
        $this->assertEquals(Actions::IGNORE, $firstFailedElement->getAction());
        $this->assertEquals(Types::FAIL, $firstFailedElement->getType());

        /**
         * There must be only one success type object.
         */
        $firstSucceedElement = current($collector->getSucceed());
        $this->assertCount(1, $collector->getSucceed());
        $this->assertEquals(Department::class, $firstSucceedElement->getClassName());
        $this->assertEquals(Actions::UPDATE, $firstSucceedElement->getAction());
        $this->assertEquals(Types::SUCCESS, $firstSucceedElement->getType());

        $sorted = $collector->getGroupByType();

        /**
         * $sorted must be an array and have the "success" and "fail" keys.
         */
        $this->assertArrayHasKey(Types::SUCCESS, $sorted);
        $this->assertArrayHasKey(Types::FAIL, $sorted);

        $counters = $collector->getCounters();

        /**
         * $counter is an array containing quantities of Types::FAIL and Types::SUCCESS
         * ex. [
         *          "success" => 0,
         *          "fail" => 1,
         *     ]
         */
        $this->assertEquals(1, $counters[Types::FAIL]);
    }
}
