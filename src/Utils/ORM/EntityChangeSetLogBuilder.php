<?php

declare(strict_types=1);

namespace App\Utils\ORM;

use App\Entity\Types\LoggableEntityInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;
use Doctrine\Common\Annotations\AnnotationException;

/**
 * Class EntityChangeSetLogBuilder
 */
class EntityChangeSetLogBuilder
{
    /**
     * @var string
     */
    private const EMPTY_PREVIOUS_VALUE_MESSAGE = 'brak';

    /**
     * @var string
     */
    private const EMPTY_NEXT_VALUE_MESSAGE = 'brak';

    /**
     * @var string
     */
    private const TRUE_MESSAGE = 'prawda';

    /**
     * @var string
     */
    private const FALSE_MESSAGE = 'fałsz';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var null|User
     */
    private $currentUser = null;

    /**
     * Logs storage.
     *
     * @var ArrayCollection
     */
    public $logsCollection;

    /**
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $token = $tokenStorage->getToken();
        if (null !== $token) {
            $this->currentUser = $entityManager
                ->getRepository(User::class)
                ->findOneBy([
                    'username' => $token->getUser()->getUsername()
                ])
            ;
        }

        $this->logsCollection = new ArrayCollection();
    }

    /**
     * Create log entities.
     *
     * @param LoggableEntityInterface $entity
     *
     * @throws AnnotationException when any property is marked with @AnnotatedLogEntity
     *
     * @return ArrayCollection
     */
    public function build(LoggableEntityInterface $entity): ArrayCollection
    {
        $propertiesToLog = EntityLogAnnotationReader::getPropertiesToLog(get_class($entity));
        if (empty($propertiesToLog)) {
            throw new AnnotationException('There is any property marked with @AnnotatedLogEntity');
        }

        $changeSet = $this->getEntityChangeSet($entity);
        foreach ($changeSet as $key => $value) {
            if (!array_key_exists($key, $propertiesToLog)) {
                continue;
            }

            $log = EntityLogAnnotationReader::getEntityLogClassInstance(get_class($entity));
            $log
                ->setOwner($this->currentUser)
                ->setLogDate(new DateTime())
                ->setNotice($this->prepareNotice(
                    $propertiesToLog[$key]['message'],
                    $value
                ))
                ->setElementTrigger($key)
                ->setParent($entity)
            ;

            $this->logsCollection->add($log);
        }

        return $this->logsCollection;
    }

    /**
     * Get entity changeset.
     *
     * @return array
     */
    private function getEntityChangeSet($entity): array
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();

        return $unitOfWork->getEntityChangeSet($entity);
    }

    /**
     * Prepares notice by format.
     * PreUpdateEventArgs::getEntityChangeSet() returns array of old and new value (index 0 and 1 - $changedValues)
     *
     * @param string $noticeFormat
     * @param array $changedValues
     *
     * @return string
     */
    private function prepareNotice(string $noticeFormat, array $changedValues): string
    {
        $oldValue = $changedValues[0];
        $newValue = $changedValues[1];

        if (is_array($oldValue)) {
            $oldValue = implode(', ', $oldValue);
        }

        if (is_array($newValue)) {
            $newValue = implode(', ', $newValue);
        }

        if (is_bool($oldValue)) {
            $oldValue = $this->boolToMessage($oldValue);
        }

        if (is_bool($newValue)) {
            $newValue = $this->boolToMessage($newValue);
        }

        return vsprintf($noticeFormat, [
            empty($oldValue)? self::EMPTY_PREVIOUS_VALUE_MESSAGE : $oldValue,
            empty($newValue)? self::EMPTY_NEXT_VALUE_MESSAGE : $newValue,
        ]);
    }

    /**
     * Parse bool to readable string.
     *
     * @param boolean $value
     *
     * @return string
     */
    private function boolToMessage(bool $value): string
    {
        if ($value) {
            return self::TRUE_MESSAGE;
        }

        return self::FALSE_MESSAGE;
    }
}
