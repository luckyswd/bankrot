<?php

declare(strict_types=1);

namespace App\DataFixtures\Traits;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

trait FixtureAwareTestTrait
{
    /** @var array<string, Loader> */
    private array $loaders = [];

    /**
     * @param class-string<FixtureInterface>[] $classes
     */
    public function addFixtures(array $classes, ?EntityManagerInterface $em = null): void
    {
        $em ??= $this->getDefaultEm();
        $loader = $this->getLoader($em);

        foreach ($classes as $class) {
            $loader->addFixture($this->resolveFixture($class));
        }
    }

    public function executeFixtures(?EntityManagerInterface $em = null, bool $append = true): void
    {
        $em ??= $this->getDefaultEm();

        $purger = new ORMPurger($em);

        $executor = new ORMExecutor($em, $purger);
        $fixtures = $this->getLoader($em)->getFixtures();

        if (!$fixtures) {
            return;
        }

        $executor->execute($fixtures, $append);
    }

    private function getLoader(EntityManagerInterface $em): Loader
    {
        $key = \spl_object_hash($em);

        return $this->loaders[$key] ??= new Loader();
    }

    private function resolveFixture(string $class): FixtureInterface
    {
        $container = $this->getTestContainer();
        $fixture = $container->has($class) ? $container->get($class) : new $class();

        if (!$fixture instanceof FixtureInterface) {
            throw new \RuntimeException("Fixture {$class} must implement " . FixtureInterface::class);
        }

        return $fixture;
    }

    private function getDefaultEm(): EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getTestContainer()->get('doctrine')->getManager();

        return $em;
    }

    private function getTestContainer(): ContainerInterface
    {
        /** @var ContainerInterface $container */
        $container = (static::class)::getContainer();

        return $container;
    }
}
