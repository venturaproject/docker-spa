<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ObjectRepository;
use LogicException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DatabaseTestCase extends WebTestCase
{
    protected static ?KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        static::$client = static::createClient();

        // We are sure that kernel is booted at this point, see createClient().
        if ('test' !== self::$kernel->getEnvironment()) {
            throw new LogicException('Execution only in Test environment possible!');
        }

        $this->initDatabase();
    }

    protected function tearDown(): void
    {
        $this->getEntityManager()->close();

        static::$client = null;

        parent::tearDown();
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\KernelBrowser|null
     */
    protected static function getReusableClient(): ?KernelBrowser
    {
        if (isset(static::$client)) {
            return static::$client;
        }

        static::$client = static::createClient();

        return static::$client;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        $client = static::getReusableClient();

        return $client->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @param string $class - entity class.
     * @return \Doctrine\ORM\EntityRepository|\Doctrine\Persistence\ObjectRepository
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    protected function getRepository(string $class): EntityRepository|ObjectRepository
    {
        return $this->getEntityManager()->getRepository($class);
    }

    /**
     * @param string $class - repository class.
     * @return \Doctrine\ORM\EntityRepository|\Doctrine\Persistence\ObjectRepository
     */
    protected function getNativeRepository(string $class): EntityRepository|ObjectRepository
    {
        return static::getContainer()->get($class);
    }

    /**
     * Init in-memory database.
     *
     * @return void
     */
    private function initDatabase(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getEntityManager();
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metaData);
    }

}
