<?php

namespace App\Tests\MessageHandler;

use App\Entity\WebSite;
use App\Message\CrawlMessage;
use App\MessageHandler\CrawlMessageHandler;
use App\Repository\WebSiteRepository;
use App\Service\CrawlerService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class CrawlMessageHandlerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private WebSiteRepository $repository;
    private CrawlerService $crawlerService;
    private CrawlMessageHandler $handler;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->repository = $container->get(WebSiteRepository::class);
        $this->crawlerService = $container->get(CrawlerService::class);

        $this->handler = new CrawlMessageHandler(
            $this->repository,
            $this->crawlerService
        );

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

    public function testInvoke(): void
    {
        // Create a real website entity and persist it
        $website = new WebSite();
        $website->setUrl('https://example.com');
        $website->setDomain('example.com');
        $website->setStatus('waiting');

        $this->repository->save($website);
        $this->entityManager->flush();

        // Get the real ID
        $websiteId = $website->getId();
        $this->assertNotNull($websiteId);

        // Create and handle the message
        $message = new CrawlMessage($websiteId);
        ($this->handler)($message);

        // Refresh the entity from database
        $this->entityManager->refresh($website);

        // Assert the website was processed correctly
        $this->assertEquals('completed', $website->getStatus());
        $this->assertNotNull($website->getRequestDuration());
        $this->assertGreaterThan(0, $website->getHtmlTagCount());
    }

    public function testInvokeWithNonexistentWebsite(): void
    {
        // Using a non-existent ID (very large number unlikely to exist)
        $nonExistentId = 999999;

        // Verify the website doesn't exist
        $nonExistentWebsite = $this->repository->find($nonExistentId);
        $this->assertNull($nonExistentWebsite);

        // Create a message with non-existent ID
        $message = new CrawlMessage($nonExistentId);

        // This should not throw an exception
        ($this->handler)($message);

        // No assertions needed beyond confirming no exception thrown
        $this->assertTrue(true);
    }

    public function testInvokeWithInvalidUrl(): void
    {
        // Create a website with invalid URL
        $website = new WebSite();
        $website->setUrl('not-a-valid-url');
        $website->setDomain('example.com');
        $website->setStatus('waiting');

        $this->repository->save($website);
        $this->entityManager->flush();

        $websiteId = $website->getId();
        $this->assertNotNull($websiteId);

        // Create and handle the message
        $message = new CrawlMessage($websiteId);
        ($this->handler)($message);

        // Refresh the entity from database
        $this->entityManager->refresh($website);

        // Assert the status is failed
        $this->assertEquals('failed', $website->getStatus());
    }
}
