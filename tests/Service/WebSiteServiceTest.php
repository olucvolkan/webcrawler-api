<?php

namespace App\Tests\Service;

use App\Entity\WebSite;
use App\Message\CrawlMessage;
use App\Repository\WebSiteRepository;
use App\Service\WebSiteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class WebSiteServiceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private WebSiteRepository $repository;
    private WebSiteService $webSiteService;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->repository = $container->get(WebSiteRepository::class);
        $this->messageBus = $container->get(MessageBusInterface::class);
        $this->webSiteService = $container->get(WebSiteService::class);

        // Start transaction for test isolation
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Roll back transaction to isolate tests
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }

        parent::tearDown();
    }

    public function testCreateWebSite(): void
    {
        // Test URL
        $url = 'https://example.com';

        // Execute the service method
        $website = $this->webSiteService->createWebSite($url);

        // Ensure the entity manager has the latest data
        $this->entityManager->flush();
        $this->entityManager->refresh($website);

        // Assertions
        $this->assertInstanceOf(WebSite::class, $website);
        $this->assertEquals($url, $website->getUrl());
        $this->assertEquals('example.com', $website->getDomain());
        $this->assertEquals('waiting', $website->getStatus());
        $this->assertNotNull($website->getId());

        // Verify the entity is actually in the database
        $storedWebsite = $this->repository->find($website->getId());
        $this->assertNotNull($storedWebsite);
        $this->assertEquals($url, $storedWebsite->getUrl());
    }

    public function testCreateWebSiteWithSubdomain(): void
    {
        // Test URL with subdomain
        $url = 'https://api.github.com';

        // Execute the service method
        $website = $this->webSiteService->createWebSite($url);

        // Ensure the entity manager has the latest data
        $this->entityManager->flush();

        // Assertions
        $this->assertEquals($url, $website->getUrl());
        $this->assertEquals('api.github.com', $website->getDomain());
        $this->assertEquals('waiting', $website->getStatus());
        $this->assertNotNull($website->getId());
    }

    public function testCreateWebSiteWithInvalidUrl(): void
    {
        $url = 'not-a-valid-url';

        $website = $this->webSiteService->createWebSite($url);

        $this->entityManager->flush();

        $this->assertEquals($url, $website->getUrl());
        $this->assertEquals('', $website->getDomain()); // Domain should be empty for invalid URL
        $this->assertEquals('waiting', $website->getStatus());
        $this->assertNotNull($website->getId());
    }
}
