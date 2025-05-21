<?php

namespace App\Tests\Service;

use App\Service\CrawlerService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CrawlerServiceTest extends KernelTestCase
{
    private CrawlerService $crawlerService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->crawlerService = static::getContainer()->get(CrawlerService::class);
    }

    public function testExtractDomain(): void
    {
        // Test cases with expected outcomes
        $testCases = [
            'https://example.com' => 'example.com',
            'http://www.example.com' => 'example.com',
            'https://www.example.com/page' => 'example.com',
            'http://subdomain.example.com' => 'subdomain.example.com',
            'https://example.co.uk' => 'example.co.uk',
            'example.com' => '', // Invalid URL, no host
        ];

        foreach ($testCases as $url => $expectedDomain) {
            $domain = $this->crawlerService->extractDomain($url);
            $this->assertEquals($expectedDomain, $domain, "Failed extracting domain from $url");
        }
    }

    public function testCrawl(): void
    {
        // Test crawling a real website
        $url = 'https://volkanoluc.com';
        $result = $this->crawlerService->crawl($url);

        // Check the structure of the result
        $this->assertArrayHasKey('requestDuration', $result);
        $this->assertArrayHasKey('htmlTagCount', $result);

        // Validate result types and values
        $this->assertIsFloat($result['requestDuration']);
        $this->assertIsInt($result['htmlTagCount']);

        // A real website should take some time to fetch and should have HTML tags
        $this->assertGreaterThan(0, $result['requestDuration']);
        $this->assertGreaterThan(0, $result['htmlTagCount']);

        echo "\nCrawled volkanoluc.com:";
        echo "\n- Request duration: " . $result['requestDuration'] . " seconds";
        echo "\n- HTML tag count: " . $result['htmlTagCount'] . " tags\n";
    }

    public function testCrawlInvalidUrl(): void
    {
        $this->expectException(\Exception::class);
        $this->crawlerService->crawl('not-a-valid-url');
    }
}
