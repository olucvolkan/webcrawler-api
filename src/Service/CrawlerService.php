<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class CrawlerService
{
    public function crawl(string $url): array
    {
        $client = HttpClient::create();
        $startTime = microtime(true);

        $response = $client->request('GET', $url, [
            'timeout' => 30,
            'max_redirects' => 5,
        ]);

        $content = $response->getContent();
        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        $crawler = new Crawler($content);
        $htmlTagCount = count($crawler->filter('*'));

        return [
            'requestDuration' => round($duration, 3),
            'htmlTagCount' => $htmlTagCount,
        ];
    }

    public function extractDomain(string $url): string
    {
        $parsedUrl = parse_url($url);
        $domain = $parsedUrl['host'] ?? '';

        // Remove 'www.' if present
        if (substr($domain, 0, 4) === 'www.') {
            $domain = substr($domain, 4);
        }

        return $domain;
    }
}
