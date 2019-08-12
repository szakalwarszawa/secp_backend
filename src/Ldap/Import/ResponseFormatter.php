<?php

declare(strict_types=1);

namespace App\Ldap\Import;

use App\Entity\FakeLdapImport;
use App\Ldap\Import\Updater\Result\Collector;
use InvalidArgumentException;
use App\Ldap\Constants\ArrayResponseFormats;
use App\Utils\ConstantsUtil;
use Doctrine\Common\Collections\ArrayCollection;

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
     * Converts Collector`s elements to FakeLdapImport object.
     *
     * @param array|Collector[]
     * @param int $format
     *
     * @throws InvalidArgumentException when $results element is not a Collector object.
     *
     * @return ArrayCollection
     */
    public static function format(array $results): ArrayCollection
    {
        $resultsCollection = new ArrayCollection();
        foreach ($results as $key => $result) {
            $supportedClass = self::supports();
            if (!$result instanceof $supportedClass) {
                throw new InvalidArgumentException(sprintf('Instance of %s expected.', $supportedClass));
            }

            $failed = $result->getFailed();
            $succeed = $result->getSucceed();
            $fakeLdapImport = new FakeLdapImport();
            $fakeLdapImport
                ->setResourceName($key)
                ->setFailedCount(count($failed))
                ->setSucceedCount(count($succeed))
                ->setFailedDetails($failed)
                ->setSucceedDetails($succeed)
            ;

            $resultsCollection->add($fakeLdapImport);
        }

        return $resultsCollection;
    }
}
