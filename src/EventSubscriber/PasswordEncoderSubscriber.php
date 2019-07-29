<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class PasswordEncoderSubscriber
 * @package App\EventSubscriber
 */
class PasswordEncoderSubscriber implements EventSubscriber
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * PasswordEncoderSubscriber constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            'prePersist',
            'preUpdate',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        if (false === $entity instanceof User) {
            return;
        }
        /* @var $entity User */
        $this->encodePassword($entity);
    }

    /**
     * @param User $user
     */
    private function encodePassword(User $user): void
    {
        if (empty($user->getPlainPassword())) {
            return;
        }
        $encoded = $this->passwordEncoder->encodePassword(
            $user,
            $user->getPlainPassword()
        );
        $user->setPassword($encoded);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        if (false === $entity instanceof User) {
            return;
        }
        /* @var $entity User */
        $this->encodePassword($entity);
    }
}
