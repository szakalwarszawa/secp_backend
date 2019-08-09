<?php

declare(strict_types=1);

namespace App\Ldap\Import;

use App\Ldap\Import\Updater\Result\Collector;
use InvalidArgumentException;
use App\Ldap\Constants\ArrayResponseFormats;
use App\Utils\ConstantsUtil;

/**
 * Class ResponseFormatter
 */
class ResponseFormatter
{
    /**
     * Supported class by this formatter.
     *
     * @return string
     */
    private static function supports(): string
    {
        return Collector::class;
    }

    /**
     * @param array|Collector[]
     * @param int $format
     *
     * @throws InvalidArgumentException when $results element is not a Collector object.
     */
    public static function format(array $results, int $format)
    {
        ConstantsUtil::constCheckValue($format, ArrayResponseFormats::class);

        $formattedResults = [];
        foreach ($results as $key => $result) {
            $supportedClass = self::supports();
            if (!$result instanceof $supportedClass) {
                throw new InvalidArgumentException(sprintf('Instance of %s expected.', $supportedClass));
            }

            switch ($format) {
                case ArrayResponseFormats::SORTED_SUCCEED_FAILED:
                    $formattedResults[$key] = $result
                        ->getSorted();
                    break;
                case ArrayResponseFormats::COUNTER_SUCCEED_DETAILED_FAILED:
                    $result->forceJoinFailures();
                    $formattedResults[$key] = $result
                        ->getCounters();
                    break;
                case ArrayResponseFormats::COUNTER_SUCCEED_FAILED:
                    $formattedResults[$key] = $result
                        ->getCounters();
                    break;
                case ArrayResponseFormats::ONLY_FAILED:
                    $formattedResults[$key] = $result
                        ->getFailed();
                    break;
                case ArrayResponseFormats::ONLY_SUCCEED:
                    $formattedResults[$key] = $result
                        ->getSucceed();
                    break;
            }
        }

        return $formattedResults;
    }
}
