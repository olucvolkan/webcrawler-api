<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class CrawlerService
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function crawl(string $url): array
    {
        $this->logger->info('Starting crawl process', ['url' => $url]);

        try {
            $client = HttpClient::create();
            $this->logger->debug('HTTP client created');

            $startTime = microtime(true);
            $this->logger->info('Sending HTTP request', ['url' => $url]);

            $response = $client->request('GET', $url, [
                'timeout' => 30,
                'max_redirects' => 5,
            ]);

            $this->logger->debug('Response received', [
                'status_code' => $response->getStatusCode(),
                'headers' => $response->getHeaders()
            ]);

            $content = $response->getContent();
            $endTime = microtime(true);
            $duration = $endTime - $startTime;

            $this->logger->info('Content retrieved', [
                'content_length' => strlen($content),
                'duration' => round($duration, 3)
            ]);

            $crawler = new Crawler($content);
            $htmlTagCount = count($crawler->filter('*'));

            $this->logger->info('HTML parsing completed', [
                'tag_count' => $htmlTagCount
            ]);

            $result = [
                'requestDuration' => round($duration, 3),
                'htmlTagCount' => $htmlTagCount,
            ];

            $this->logger->info('Crawl process completed successfully', $result);
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Error during crawl process', [
                'error' => $e->getMessage(),
                'url' => $url,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function extractDomain(string $url): string
    {
        $this->logger->debug('Extracting domain', ['url' => $url]);

        try {
            $parsedUrl = parse_url($url);
            $domain = $parsedUrl['host'] ?? '';

            if (substr($domain, 0, 4) === 'www.') {
                $domain = substr($domain, 4);
            }

            $this->logger->info('Domain extracted successfully', [
                'original_url' => $url,
                'extracted_domain' => $domain
            ]);

            return $domain;
        } catch (\Exception $e) {
            $this->logger->error('Error extracting domain', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
