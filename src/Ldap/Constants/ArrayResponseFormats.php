<?php

declare(strict_types=1);

namespace App\Ldap\Constants;

/**
 * Class ArrayResponseFormats
 */
class ArrayResponseFormats
{
    /**
     * Simplest response.
     * Array containg quantities of fail/success grouped by key.
     * ex.
     *  {
     *      "department_section: {
     *          "success": 123,
     *          "fail": 5
     *      },
     *      "users" {
     *          ...
     *      }
     *  }
     */
    public const COUNTER_SUCCESS_FAILED = 1;

    /**
     * Mix of COUNTER_SUCCESS_FAILED and COUNTER_SUCCESS_DETAILED_FAILED.
     * Key "success" will be printed as COUNTER_SUCCESS_FAILED,
     * Key "fail" will be printed as COUNTER_SUCCESS_DETAILED_FAILED
     */
    public const COUNTER_SUCCESS_DETAILED_FAILED = 2;

    /**
     * Array containing details grouped by key (ex. users or department_section)
     * with all results (mixed fail and success).
     * ex.
     * {
     *  "users":
     *      {
     *          className: "App\..\User"
     *          type: "success"
     *          ...
     *      },
     *      {
     *          className: "App\..User"
     *          type: "failed"
     *          ...
     *      }
     *      ...
     *  "department_section":
     *      {
     *          ...
     *      }
     * }
     */
    public const SORTED_SUCCESS_FAILED = 3;

    /**
     * Like COUNTER_SUCCESS_DETAILED_FAILED (detailed) but only succeed.
     */
    public const ONLY_SUCCEED = 4;

    /**
     *     /**
     * Like COUNTER_SUCCESS_DETAILED_FAILED (detailed) but only failed.
     */
    public const ONLY_FAILED = 5;
}
