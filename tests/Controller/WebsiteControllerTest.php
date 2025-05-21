<?php

namespace App\Tests\Controller;

use App\Entity\WebSite;
use App\Repository\WebSiteRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WebsiteControllerTest extends WebTestCase
{
    private $client;
    private WebSiteRepository $websiteRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->websiteRepository = static::getContainer()->get(WebSiteRepository::class);

        // Clear the database
        $this->websiteRepository->createQueryBuilder('w')
            ->delete()
            ->getQuery()
            ->execute();
    }

    public function testListWebsites(): void
    {
        // Create test websites
        $website1 = new WebSite();
        $website1->setUrl('https://example1.com');
        $website1->setDomain('example1.com');
        $website1->setStatus('completed');
        $website1->setHtmlTagCount(100);
        $website1->setRequestDuration(1.5);

        $website2 = new WebSite();
        $website2->setUrl('https://example2.com');
        $website2->setDomain('example2.com');
        $website2->setStatus('completed');
        $website2->setHtmlTagCount(150);
        $website2->setRequestDuration(2.0);

        $this->websiteRepository->save($website1);
        $this->websiteRepository->save($website2);

        // Test list all websites
        $this->client->request('GET', '/api/websites');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);

         $this->assertCount(2, $content['data']['websites']);

        // Test filtering by domain
        $this->client->request('GET', '/api/websites?domain=example1.com');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);

        $this->assertCount(1, $content['data']['websites']);
        $this->assertEquals('example1.com', $content['data']['websites'][0]['domain']);
    }

    public function testListWebsitesWithInvalidDomain(): void
    {
        // Test with invalid domain format
        $this->client->request('GET', '/api/websites?domain=invalid-domain');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('errors', $content);

        // Test with too long domain
        $longDomain = str_repeat('a', 256) . '.com';
        $this->client->request('GET', '/api/websites?domain=' . $longDomain);
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('errors', $content);
    }

    public function testShowWebsite(): void
    {
        // Create test website
        $website = new WebSite();
        $website->setUrl('https://example.com');
        $website->setDomain('example.com');
        $website->setStatus('completed');
        $website->setHtmlTagCount(100);
        $website->setRequestDuration(1.5);

        $this->websiteRepository->save($website);

        // Test show endpoint
        $this->client->request('GET', '/api/websites/' . $website->getId());
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);

        $this->assertEquals('example.com', $content['data']['website']['domain']);

        // Test not found case
        $this->client->request('GET', '/api/websites/99999');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}
