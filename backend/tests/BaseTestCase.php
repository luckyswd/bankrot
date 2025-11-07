<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\Traits\FixtureAwareTestTrait;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseTestCase extends WebTestCase
{
    use FixtureAwareTestTrait;

    protected static EntityManagerInterface $em;
    protected static bool $transactionStarted = false;
    protected ?JWTTokenManagerInterface $jwtManager = null;
    protected ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->client->setServerParameter('CONTENT_TYPE', 'application/json;charset=utf-8');

        $container = $this->client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();
        self::$em = $em;
        self::$em->beginTransaction();

        $this->jwtManager = $container->get(JWTTokenManagerInterface::class);
    }

    protected function tearDown(): void
    {
        self::$em->getConnection()->rollBack();

        parent::tearDown();
    }

    protected function getUser(string $reference): User
    {
        return self::$em->getRepository(User::class)->findOneBy([
            'username' => match ($reference) {
                'user1' => 'test_user1',
                'user2' => 'test_user2',
                'manager' => 'test_manager',
                default => throw new \InvalidArgumentException("Unknown user reference: {$reference}"),
            },
        ]);
    }

    protected function getAuthToken(User $user): string
    {
        return $this->jwtManager->create($user);
    }
}
