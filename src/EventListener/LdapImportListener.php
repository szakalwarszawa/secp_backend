<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Ldap\Event\LdapImportedEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Ldap\Import\Updater\Result\Collector;
use InvalidArgumentException;
use App\Entity\LdapImportLog;
use DateTime;

/**
 * Class LdapImportListener
 */
class LdapImportListener
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Starts work on the object.
     *
     * @param LdapImportedEvent $event;
     *
     * @return void
     */
    public function onLdapImported(LdapImportedEvent $event): void
    {
        $results = $event->getResults();
        $supportedClass = $this->supports();
        foreach ($results as $result) {
            if (!$result instanceof $supportedClass) {
                throw new InvalidArgumentException(sprintf('Instance of %s expected.', $supportedClass));
            }
        }

        $this->createLog($results);

        $event->stopPropagation();
    }

    /**
     * Saves the serialized import result to the database.
     *
     * @param array $results
     *
     * @return void
     */
    private function createLog(array $results): void
    {
        $ldapImportLog = new LdapImportLog();
        $ldapImportLog
            ->setResult(serialize($results))
            ->setCreatedAt(new DateTime())
        ;

        $this
            ->entityManager
            ->persist($ldapImportLog)
        ;
        $this
            ->entityManager
            ->flush();
        ;
    }

    /**
     * Supported class by this event.
     *
     * @return string
     */
    private function supports(): string
    {
        return Collector::class;
    }
}
