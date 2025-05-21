<?php

namespace App\Tests\Controller;

use App\Entity\WebSite;
use App\Repository\WebSiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CrawlerControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private WebSiteRepository $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = static::getContainer()->get(WebSiteRepository::class);

        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }

        parent::tearDown();
    }

    public function testCrawlEndpoint(): void
    {
        // Send a request to the API
        $this->client->request(
            'POST',
            '/api/crawler/crawl',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['url' => 'https://example.com'])
        );

        // Assert response is successful
        $this->assertResponseIsSuccessful();

        // Check response content
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        // Structure checks
        $this->assertArrayHasKey('status', $responseContent);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);

        // Value checks
        $this->assertEquals('success', $responseContent['status']);
        $this->assertEquals('We are crawling website now.', $responseContent['message']);
        $this->assertArrayHasKey('id', $responseContent['data']);
        $this->assertArrayHasKey('status', $responseContent['data']);
        $this->assertEquals('waiting', $responseContent['data']['status']);

        // Check database
        $id = $responseContent['data']['id'];
        $website = $this->repository->find($id);

        $this->assertNotNull($website);
        $this->assertEquals('example.com', $website->getDomain());
        $this->assertEquals('https://example.com', $website->getUrl());
    }

    public function testCrawlEndpointWithInvalidUrl(): void
    {
        // Send a request with invalid URL
        $this->client->request(
            'POST',
            '/api/crawler/crawl',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['url' => 'not-a-valid-url'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('status', $responseContent);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertArrayHasKey('errors', $responseContent);

        $this->assertEquals('error', $responseContent['status']);
        $this->assertEquals('Validation failed', $responseContent['message']);
        $this->assertNotEmpty($responseContent['errors']);
    }

    public function testCrawlEndpointWithMissingUrl(): void
    {
        $this->client->request(
            'POST',
            '/api/crawler/crawl',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('error', $responseContent['status']);
        $this->assertNotEmpty($responseContent['errors']);
    }
}
