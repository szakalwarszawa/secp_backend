<?php

namespace App\Tests\EventSubscriber;

use App\Entity\User;
use App\EventSubscriber\PasswordEncoderSubscriber;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordEncoderSubscriberTest extends AbstractWebTestCase
{
    /**
     * @var PasswordEncoderSubscriber
     */
    private $subscriber;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function testConfiguration(): void
    {
        $this->assertContains('prePersist', $this->subscriber->getSubscribedEvents());
        $this->assertContains('preUpdate', $this->subscriber->getSubscribedEvents());
    }

    public function testPasswordEncoding(): void
    {
        $user = new User();
        $user->setPlainPassword('test');

        $this->assertNull($user->getPassword());

        self::$container->get('doctrine')->getManager()->persist($user);

        $this->assertStringContainsString('$argon2i', $user->getPassword());
        $this->assertTrue($this->passwordEncoder->isPasswordValid($user, 'test'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $container = self::$kernel->getContainer();
        $this->passwordEncoder = $container->get('security.password_encoder');
        $this->subscriber = new PasswordEncoderSubscriber($this->passwordEncoder);
    }
}
