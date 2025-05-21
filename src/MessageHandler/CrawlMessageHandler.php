<?php

namespace App\MessageHandler;

use App\Message\CrawlMessage;
use App\Repository\WebSiteRepository;
use App\Service\CrawlerService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CrawlMessageHandler
{
    private WebSiteRepository $webSiteRepository;
    private CrawlerService $crawlerService;

    public function __construct(
        WebSiteRepository $webSiteRepository,
        CrawlerService $crawlerService
    ) {
        $this->webSiteRepository = $webSiteRepository;
        $this->crawlerService = $crawlerService;
    }

    public function __invoke(CrawlMessage $message): void
    {
        $webSiteId = $message->getWebSiteId();
        $webSite = $this->webSiteRepository->find($webSiteId);

        if (!$webSite) {
            return;
        }

        try {
            $webSite->setStatus('processing');
            $this->webSiteRepository->save($webSite);

            $result = $this->crawlerService->crawl($webSite->getUrl());

            $webSite->setRequestDuration($result['requestDuration']);
            $webSite->setHtmlTagCount($result['htmlTagCount']);
            $webSite->setStatus('completed');

            $this->webSiteRepository->save($webSite);
        } catch (\Exception $e) {
            $webSite->setStatus('failed');
            $this->webSiteRepository->save($webSite);
        }
    }
}
